<?php

namespace ModulesPress\Core\Resolver;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Core\Core;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\DI\Attributes\Inject;
use ModulesPress\Foundation\DI\Attributes\Injectable;
use ModulesPress\Foundation\DI\Enums\Scope;

/**
 * Resolver Class
 * 
 * Resolves and validates module dependencies in the plugin. It checks for valid
 * dependencies and handles the injection process for both classes and factories.
 */
final class Resolver
{
    /**
     * @param Core $core The core system to access framework dependencies and other services.
     */
    public function __construct(
        private readonly Core $core,
    ) {}

    /**
     * Validates the dependencies of a given class.
     * 
     * This checks if all dependencies required by the class are provided by the module or
     * other imported modules. Throws an exception if any dependencies are not satisfied.
     * 
     * @param ResolvedModule $resolvedModule The module that is being resolved.
     * @param string $classToCheck The class to check for dependencies.
     * 
     * @return array List of dependencies for the class.
     * 
     * @throws ModuleResolutionException If the class is not injectable or if dependencies are unresolved.
     */
    public function validateClassDependencies(
        ResolvedModule $resolvedModule,
        string $classToCheck,
    ): array {

        // Get the dependencies of the class
        $dependencies = $this->getClassDependencies($classToCheck);

        // If there are no dependencies, return an empty array
        if (count($dependencies) === 0) return [];

        // Check if the class is injectable or if it is a REST controller
        $injectableAttrs = (new ReflectionClass($classToCheck))->getAttributes(Injectable::class);
        if (
            count($injectableAttrs) === 0 &&
            !AttributesScanner::haveAttribute(RestController::class, $classToCheck)
        ) {
            // If not injectable or a REST controller, throw an exception
            throw (new ModuleResolutionException(
                reason: "Class '" . $classToCheck . "' must be injectable to inject dependencies to it."
            ))->forClass($classToCheck);
        }

        // Validate the dependencies for the class
        $this->validateDependencies($resolvedModule, $dependencies, $classToCheck);
        return $dependencies;
    }

    /**
     * Validates the dependencies of a given factory.
     * 
     * This checks if all dependencies required by the factory are satisfied.
     * 
     * @param ResolvedModule $resolvedModule The module that is being resolved.
     * @param callable $factoryToCheck The factory function to check for dependencies.
     * 
     * @return array List of dependencies for the factory.
     */
    public function validateFactoryDependencies(
        ResolvedModule $resolvedModule,
        callable $factoryToCheck,
    ): array {
        // Get the dependencies of the factory
        $dependencies = $this->getFactoryDependencies($factoryToCheck);
        if (count($dependencies) === 0) return [];

        // Validate the factory dependencies
        $this->validateDependencies($resolvedModule, $dependencies, $factoryToCheck);
        return $dependencies;
    }

    /**
     * Validates if the dependencies for the given class or factory are satisfied.
     * 
     * This method checks if each dependency is provided by the module or imported modules.
     * If a dependency is missing, it throws an exception.
     * 
     * @param ResolvedModule $resolvedModule The module being resolved.
     * @param array $dependencies The dependencies to validate.
     * @param string|callable $factoryOrClass The class or factory being validated.
     */
    private function validateDependencies(
        ResolvedModule $resolvedModule,
        array $dependencies,
        string | callable $factoryOrClass
    ): void {

        // Determine if we're validating a class or factory
        if (is_callable($factoryOrClass)) {
            $exceptionFor = "forClassMethod";
            $injectionFor = $factoryOrClass[1]; // The factory method name
            $className = (new ReflectionMethod($factoryOrClass[0], $factoryOrClass[1]))->getDeclaringClass()->getName();
            $exceptionParams = [$className, $factoryOrClass[1]];
        } else {
            $exceptionFor = "forClass";
            $injectionFor = $factoryOrClass; // The class name
            $exceptionParams = [$factoryOrClass];
        }

        // Get the framework's provided dependencies
        $frameworkDependencies = array_keys($this->core->getFrameworkDependencies());

        // Iterate through the dependencies
        foreach ($dependencies as $dependency) {

            // Skip framework dependencies
            if (in_array($dependency, $frameworkDependencies)) continue;

            // Check if the dependency is provided by the module
            if (!in_array($dependency, $resolvedModule->getProvidedTokens())) {
                // Check if the dependency is provided by any imported module
                foreach ($resolvedModule->getImportsAsClasses() as $imported_module_class) {

                    // Resolve the imported module
                    $importedResolvedModule = $this->core->getPluginContainer()->getResolvedModuleByKey($imported_module_class);
                    if (!$importedResolvedModule) {
                        throw (new ModuleResolutionException(
                            reason: "Module '" . $imported_module_class . "' cannot be validated as it does not exist in the context or resolved."
                        ))->$exceptionFor(...$exceptionParams);
                    }

                    // If the dependency is provided by the imported module, check if it's exported
                    if (in_array($dependency, $importedResolvedModule->getProvidedTokens())) {
                        if (!in_array($dependency, $importedResolvedModule->getExports())) {
                            throw (new ModuleResolutionException(
                                reason: "Dependency '" . $dependency . "' must be exported by a module '" . $imported_module_class . "' before injecting it in '" . $injectionFor . "'."
                            ))->$exceptionFor(...$exceptionParams);
                        } else {
                            continue 2; // Dependency is valid, move to the next
                        }
                    }
                }

                // Check if the dependency is available globally
                $resolvedModuleForDependency = $this->core->getPluginContainer()->getResolvedModuleByDependencyKey($dependency);
                if ($resolvedModuleForDependency) {
                    if ($resolvedModuleForDependency->isGlobal()) {
                        // If the dependency is global, ensure it's exported
                        if (!in_array($dependency, $resolvedModuleForDependency->getExports())) {
                            throw (new ModuleResolutionException(
                                reason: "Dependency '" . $dependency . "' must be exported by a global module '" . $resolvedModuleForDependency->getClass() . "' before injecting it in '" . $injectionFor . "'."
                            ))->$exceptionFor(...$exceptionParams);
                        } else {
                            continue;
                        }
                    }
                }

                // If the dependency is not found, throw an exception
                throw (new ModuleResolutionException(
                    reason: "Undefined dependency '" . $dependency . "' must be provided by a module '" . $resolvedModule->getClass() . "' before injecting it in '" . $injectionFor . "'."
                ))->$exceptionFor(...$exceptionParams);
            }
        }
    }

    /**
     * Retrieves the dependencies of a class.
     * 
     * This method uses reflection to get the constructor dependencies of a class.
     * 
     * @param string $class The class name to check.
     * 
     * @return array List of dependencies.
     */
    private function getClassDependencies(
        string $class
    ): array {
        $provider_reflection_class = new \ReflectionClass($class);
        $constructor = $provider_reflection_class->getConstructor();
        return $constructor ? $this->getDependencies($constructor) : [];
    }

    /**
     * Retrieves the dependencies of a factory method.
     * 
     * @param callable $factory The factory method.
     * 
     * @return array List of dependencies.
     */
    private function getFactoryDependencies(
        callable $factory
    ): array {
        return $this->getDependencies(new ReflectionMethod($factory[0], $factory[1]));
    }

    /**
     * Retrieves the dependencies from a callable's parameters.
     * 
     * @param ReflectionMethod $callable The callable to inspect.
     * 
     * @return array List of dependencies.
     */
    private function getDependencies(
        ReflectionMethod $callable,
    ): array {
        $parameters = $callable->getParameters();
        $dependencies = [];
        foreach ($parameters as $parameter) {
            $injectedAttrs = $parameter->getAttributes(Inject::class);
            if (count($injectedAttrs) > 0) {
                $dependencies[] = $injectedAttrs[0]->newInstance()->getToken();
                continue;
            }
            $reflectionNamedType = $parameter->getType();
            $dependency_type = $reflectionNamedType->getName();
            $dependencies[] = $dependency_type;
        }
        return $dependencies;
    }

    /**
     * Resolves and instantiates a class, injecting its dependencies.
     * 
     * @param ResolvedModule $resolvedModule The module being resolved.
     * @param string|object $classToInstantiate The class name or an existing instance.
     * @param bool $validateDeps Whether to validate dependencies.
     * 
     * @return mixed The instantiated object.
     */
    public function resolve(
        ResolvedModule $resolvedModule,
        string | object $classToInstantiate,
        bool $validateDeps = false
    ): mixed {

        // If the class is already an object, return it as is
        if (!is_string($classToInstantiate)) return $classToInstantiate;

        // Validate dependencies if required
        if ($validateDeps) {
            $deps = $this->validateClassDependencies($resolvedModule, $classToInstantiate);
        } else {
            $deps = $this->getClassDependencies($classToInstantiate);
        }

        // Instantiate the class with the resolved dependencies
        $instance = new $classToInstantiate(...$this->inject($deps));

        // Call the onModuleInit method if it exists
        if (method_exists($instance, 'onModuleInit')) {
            call_user_func([$instance, 'onModuleInit'], $resolvedModule);
        }

        return $instance;
    }

    /**
     * Resolves and invokes a factory function, injecting dependencies.
     * 
     * @param ResolvedModule $resolvedModule The module being resolved.
     * @param callable $factory The factory function.
     * @param bool $validateDeps Whether to validate dependencies.
     * 
     * @return mixed The result of the factory function.
     */
    public function resolveFactory(
        ResolvedModule $resolvedModule,
        callable $factory,
        bool $validateDeps = false
    ): mixed {

        // Validate dependencies if required
        if ($validateDeps) {
            $deps = $this->validateFactoryDependencies($resolvedModule, $factory);
        } else {
            $deps = $this->getFactoryDependencies($factory);
        }

        // Invoke the factory with the resolved dependencies
        return $factory(...$this->inject($deps));
    }

    /**
     * Resolves the dependencies by fetching them from the container.
     * 
     * @param array $deps The list of dependencies to resolve.
     * 
     * @return array The resolved dependencies.
     */
    private function inject(
        array $deps
    ): array {
        $resolved_deps = [];
        foreach ($deps as $dep) {
            $provider = $this->core->getPluginContainer()->getProviderByDependencyKey($dep);
            if ($provider && $provider->getScope() === Scope::TRANSIENT) {
                $resolved_deps[] = $this->core->getPluginContainer()->make($dep);
            } else {
                $resolved_deps[] = $this->core->getPluginContainer()->get($dep);
            }
        }
        return $resolved_deps;
    }
}
