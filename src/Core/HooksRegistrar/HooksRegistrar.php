<?php

namespace ModulesPress\Core\HooksRegistrar;

use ModulesPress\Common\Exceptions\HttpException\UnauthorizedHttpException;
use ModulesPress\Foundation\Hookable\Attributes\Add_Filter;
use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Core\Core;
use ModulesPress\Core\ExceptionHandler\ExceptionHandler;
use ModulesPress\Core\ExceptionHandler\ResolvedFilter;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\ExecutionContext\HookContext;
use ModulesPress\Core\Resolver\ResolvedModule;

/**
 * The HooksRegistrar class manages the registration of hookable components (actions and filters) within the ModulesPress framework. 
 * It scans the plugin container for hookable components and registers them with WordPress, applying any necessary guards, 
 * exception filters, and checks before executing the associated callback method.
 */
final class HooksRegistrar
{
    /**
     * HooksRegistrar constructor.
     *
     * @param Core $core The core instance responsible for the plugin container and resolution.
     * @param ExceptionHandler $exceptionHandler Handles exceptions that occur during hook execution.
     * @param ExecutionContext $executionContext The context for the current execution (including hooks).
     */
    public function __construct(
        private readonly Core $core,
        private readonly ExceptionHandler $exceptionHandler,
        private readonly ExecutionContext $executionContext,
    ) {}

    /**
     * Registers all hookables for the plugin, using either the `add_filter` or `add_action` WordPress functions.
     *
     * @return HooksRegistrar The current instance, allowing for method chaining.
     */
    public function registerHookables(): HooksRegistrar
    {
        // Loop through each hookable component from the plugin container
        foreach ($this->core->getPluginContainer()->getHooks() as $hookComponent) {

            // Determine whether the hook is a filter or action
            $hookable = $hookComponent->getHookable();
            $hookFunction = $hookable instanceof Add_Filter ? 'add_filter' : 'add_action';

            // Register the hook
            $hookFunction(
                $hookable->getHookName(),
                function (...$args) use ($hookComponent, $hookFunction, $hookable) {
                    // Create a new hook context
                    $hookContext = new HookContext(
                        $hookable,
                        $hookComponent->getClassReflection(),
                        $hookComponent->getMethodReflection(),
                        func_get_args()
                    );

                    // Process the hook and return the result if it's a filter
                    $result = self::processIncomingHook(
                        $hookComponent->getProvider(),
                        $hookComponent->getResolvedModule(),
                        $hookContext
                    );

                    // Return the result if it's a filter, otherwise, no return is needed for actions
                    if ($hookFunction === 'add_filter') {
                        return $result;
                    }
                },
                priority: $hookable->getPriority(),
                accepted_args: 50 // Maximum accepted arguments for the hook
            );
        }

        return $this;
    }

    /**
     * Processes the incoming hook. It applies guards, exception filters, and checks before invoking the 
     * hook method from the class.
     *
     * @param Provider $provider The provider associated with the hook.
     * @param ResolvedModule $resolvedModule The resolved module that contains the hook method.
     * @param HookContext $hookContext The context for the current hook execution.
     * @return mixed The result of the hook execution, or the default value if certain checks fail.
     */
    private function processIncomingHook(Provider $provider, ResolvedModule $resolvedModule, HookContext $hookContext)
    {
        try {
            // Push the hook context onto the execution stack
            $this->executionContext->pushHookContext($hookContext);

            // Check for any exception filters defined for this class and method
            $exceptionFiltersClasses = AttributesScanner::scanUseExceptionFilters(
                class: $hookContext->getClassReflection(),
                method: $hookContext->getMethodReflection()
            );

            // If there are exception filters, add them to the exception handler
            if (!empty($exceptionFiltersClasses)) {
                $filtersKey = $hookContext->getClassReflection()->getName() . '::' . $hookContext->getMethodReflection()->getName();
                foreach ($exceptionFiltersClasses as $exceptionFilter) {
                    $this->exceptionHandler->addExceptionFilter(
                        new ResolvedFilter(
                            key: $filtersKey,
                            filter: $exceptionFilter,
                            resolvedModule: $resolvedModule
                        )
                    );
                }
            }

            // Apply guards to check if the current user is authorized to proceed with the hook
            $guards = AttributesScanner::scanUseGuards(
                method: $hookContext->getMethodReflection()
            );

            foreach ($guards as $guardClass) {
                $guard = $this->core->getResolver()->resolve($resolvedModule, $guardClass, true);
                if (!$guard->canActivate($this->executionContext)) {
                    // Throw an unauthorized exception if the guard denies access
                    throw (new UnauthorizedHttpException())->forClassMethod(
                        $hookContext->getClassReflection()->getName(),
                        $hookContext->getMethodReflection()->getName()
                    );
                }
            }

            // Perform any checks before invoking the hook method
            $checks = AttributesScanner::scanUseChecks(
                method: $hookContext->getMethodReflection()
            );

            foreach ($checks as $checker) {
                foreach ($checker->getChecks() as $checkClass) {
                    $check = $this->core->getResolver()->resolve($resolvedModule, $checkClass, true);
                    if (!$check->canActivate($this->executionContext)) {
                        // Return the default argument value if a check fails
                        if (count($hookContext->getArgs()) > 0) {
                            return $hookContext->getArgs()[$checker->getDefaultReturnArgNo()];
                        }
                    }
                }
            }

            // Get the class instance for the provider and call the hook method
            $class = $this->core->getPluginContainer()->get($provider->getProvidedToken());
            $result = call_user_func(
                [
                    $class,
                    $hookContext->getMethodReflection()->getName()
                ],
                ...$hookContext->getArgs(),
            );

            // Pop the hook context off the execution stack
            $this->executionContext->popHookContext();

            // Remove exception filters if they were applied
            if (!empty($exceptionFiltersClasses)) {
                $this->exceptionHandler->removeExceptionFilters($filtersKey);
            }

            return $result;
        } catch (\Throwable $th) {
            // Handle any exceptions that occur during the hook processing
            $this->exceptionHandler->handleException($th);
        }
    }
}
