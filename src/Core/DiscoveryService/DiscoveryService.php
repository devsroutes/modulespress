<?php

namespace ModulesPress\Core\DiscoveryService;

use ReflectionClass;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Core\Core;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Components\HookComponent;
use ModulesPress\Foundation\Components\RouteComponent;
use ModulesPress\Foundation\Components\ViewComposeComponent;
use ModulesPress\Foundation\Components\ViewDirectiveComponent;
use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\Module\Attributes\Module;
use ModulesPress\Foundation\Module\Contracts\DynamicModule;
use ModulesPress\Foundation\Module\ModulesPressModule;

/**
 * Class DiscoveryService
 *
 * Provides functionality for discovering modules, routes, hooks, view composers, and directives in the framework.
 * The service resolves dependencies, scans attributes, and ensures proper module hierarchy.
 *
 */
final class DiscoveryService
{
    /**
     * DiscoveryService constructor.
     *
     * @param Core $core The core instance of the framework.
     */
    public function __construct(
        private readonly Core $core
    ) {}

    /**
     * Discover and resolve modules.
     *
     * @param string $moduleClass The module class to discover.
     * @return ResolvedModule[] List of resolved modules.
     */
    public function discoverModules(string $moduleClass)
    {
        return $this->loadModule(null, $moduleClass);
    }

    /**
     * Discover framework components from resolved modules.
     *
     * @param ResolvedModule[] $resolvedModules List of resolved modules.
     * @return DiscoveryResult The result containing all discovered components.
     */
    public function discoverComponents(array $resolvedModules): DiscoveryResult
    {
        $routesComponents = [];
        $hooksComponents = [];
        $viewComposersComponents = [];
        $viewDirectivesComponents = [];

        foreach ($resolvedModules as $resolvedModule) {
            foreach ($resolvedModule->getProviders() as $provider) {
                if (!$provider->hasUsableClass()) continue;

                $providerClassReflection = new ReflectionClass($provider->getUsableClass());
                $methods = $providerClassReflection->getMethods();

                foreach ($methods as $providerMethodReflection) {
                    if ($providerMethodReflection->getName() === '__construct') continue;

                    $this->discoverHookComponents(
                        $resolvedModule,
                        $provider,
                        $providerClassReflection,
                        $providerMethodReflection,
                        $hooksComponents
                    );

                    $this->discoverViewComposeComponents(
                        $resolvedModule,
                        $provider,
                        $providerClassReflection,
                        $providerMethodReflection,
                        $viewComposersComponents
                    );

                    $this->discoverViewDirectiveComponents(
                        $resolvedModule,
                        $provider,
                        $providerClassReflection,
                        $providerMethodReflection,
                        $viewDirectivesComponents
                    );
                }
            }

            foreach ($resolvedModule->getControllers() as $controllerClassName) {
                $controllerClassReflection = new ReflectionClass($controllerClassName);
                $controllers = AttributesScanner::scanRestControllers($controllerClassReflection);

                if (empty($controllers)) continue;
                $controller = $controllers[0];

                foreach ($controllerClassReflection->getMethods() as $controllerMethodReflection) {
                    if ($controllerMethodReflection->getName() === '__construct') continue;

                    $this->discoverRouteComponents(
                        $resolvedModule,
                        $controller,
                        $controllerClassReflection,
                        $controllerMethodReflection,
                        $routesComponents
                    );
                }
            }
        }

        return new DiscoveryResult(
            $routesComponents,
            $hooksComponents,
            $viewComposersComponents,
            $viewDirectivesComponents
        );
    }

    /**
     * Discover hook components.
     *
     * @param ResolvedModule $resolvedModule The module containing the hooks.
     * @param Provider $provider The provider being analyzed.
     * @param ReflectionClass $providerClassReflection Reflection of the provider class.
     * @param \ReflectionMethod $providerMethodReflection Reflection of the provider method.
     * @param array $hooksComponents Array to populate with HookComponent instances.
     * @return array The updated hooks components.
     */
    public function discoverHookComponents(
        ResolvedModule $resolvedModule,
        Provider $provider,
        ReflectionClass $providerClassReflection,
        \ReflectionMethod $providerMethodReflection,
        array &$hooksComponents = []
    ) {
        foreach (
            [
                ...AttributesScanner::scanAddActions($providerMethodReflection),
                ...AttributesScanner::scanAddFilters($providerMethodReflection)
            ] as $hookable
        ) {
            $hooksComponents[] = new HookComponent(
                $resolvedModule,
                $provider,
                $providerClassReflection,
                $providerMethodReflection,
                $hookable
            );
        }
        return $hooksComponents;
    }

    /**
     * Discover view compose components.
     *
     * @param ResolvedModule $resolvedModule The module containing the view composers.
     * @param Provider $provider The provider being analyzed.
     * @param ReflectionClass $providerClassReflection Reflection of the provider class.
     * @param \ReflectionMethod $providerMethodReflection Reflection of the provider method.
     * @param array $viewComposersComponents Array to populate with ViewComposeComponent instances.
     * @return array The updated view composers components.
     */
    public function discoverViewComposeComponents(
        ResolvedModule $resolvedModule,
        Provider $provider,
        ReflectionClass $providerClassReflection,
        \ReflectionMethod $providerMethodReflection,
        array &$viewComposersComponents = []
    ) {
        foreach (AttributesScanner::scanViewComposers($providerMethodReflection) as $viewCompose) {
            $viewComposersComponents[] = new ViewComposeComponent(
                $resolvedModule,
                $provider,
                $providerClassReflection,
                $providerMethodReflection,
                $viewCompose
            );
        }
        return $viewComposersComponents;
    }

    /**
     * Discover view directive components.
     *
     * @param ResolvedModule $resolvedModule The module containing the view directives.
     * @param Provider $provider The provider being analyzed.
     * @param ReflectionClass $providerClassReflection Reflection of the provider class.
     * @param \ReflectionMethod $providerMethodReflection Reflection of the provider method.
     * @param array $viewDirectivesComponents Array to populate with ViewDirectiveComponent instances.
     * @return array The updated view directives components.
     */
    public function discoverViewDirectiveComponents(
        ResolvedModule $resolvedModule,
        Provider $provider,
        ReflectionClass $providerClassReflection,
        \ReflectionMethod $providerMethodReflection,
        array &$viewDirectivesComponents = []
    ) {
        foreach (AttributesScanner::scanViewDirectives($providerMethodReflection) as $viewDirective) {
            $viewDirectivesComponents[] = new ViewDirectiveComponent(
                $resolvedModule,
                $provider,
                $providerClassReflection,
                $providerMethodReflection,
                $viewDirective
            );
        }
        return $viewDirectivesComponents;
    }

    /**
     * Discover route components.
     *
     * @param ResolvedModule $resolvedModule The module containing the routes.
     * @param RestController $controller The controller containing routes.
     * @param ReflectionClass $controllerClassReflection Reflection of the controller class.
     * @param \ReflectionMethod $controllerMethodReflection Reflection of the controller method.
     * @param array $routesComponents Array to populate with RouteComponent instances.
     * @return array The updated routes components.
     */
    public function discoverRouteComponents(
        ResolvedModule $resolvedModule,
        RestController $controller,
        \ReflectionClass $controllerClassReflection,
        \ReflectionMethod $controllerMethodReflection,
        array &$routesComponents = []
    ) {
        foreach (AttributesScanner::scanRoutes($controllerMethodReflection) as $route) {
            $routesComponents[] = new RouteComponent(
                $resolvedModule,
                $controller,
                $controllerClassReflection,
                $controllerMethodReflection,
                $route
            );
        }
        return $routesComponents;
    }

    /**
     * Resolves and loads a module and its dependencies recursively.
     *
     * @param ResolvedModule|null $resolvedParentModule The parent module that depends on the current module.
     * @param DynamicModule|string $currentModuleClass The module class to be resolved.
     * @param array<string, ResolvedModule> $resolvedModules A reference to the list of already resolved modules.
     * @return array<string, ResolvedModule> The updated list of resolved modules.
     *
     * @throws ModuleResolutionException If the module does not inherit from ModulesPressModule or a circular dependency is detected.
     */
    private function loadModule(
        ResolvedModule | null $resolvedParentModule,
        DynamicModule | string $currentModuleClass,
        array &$resolvedModules = []
    ) {
        // Handle DynamicModule instances
        if ($currentModuleClass instanceof DynamicModule) {
            $module = $currentModuleClass->register();
            $instance = $currentModuleClass;
            $currentModuleClass = $currentModuleClass::class;
        } else {
            // Reflect to get module attributes for non-DynamicModule classes
            $moduleAttributes = (new \ReflectionClass($currentModuleClass))->getAttributes(Module::class);
            $module = $moduleAttributes[0]->newInstance();
            $instance = new $currentModuleClass();
        }

        // Ensure the module implements ModulesPressModule
        if (!$instance instanceof ModulesPressModule) {
            throw (new ModuleResolutionException(
                reason: "Module class '$currentModuleClass' is not an instance of ModulesPressModule."
            ))->forClass($currentModuleClass);
        }

        // Create a ResolvedModule instance
        $currentResolvedModule = new ResolvedModule(
            $module,
            $currentModuleClass,
            $resolvedParentModule,
            $instance
        );

        // Check for circular dependencies
        if ($resolvedParentModule && $this->hasCircularDependency($currentResolvedModule, $resolvedParentModule)) {
            throw (new ModuleResolutionException(
                reason: "Circular dependency detected between modules '" .
                    $resolvedParentModule->getClass() .
                    "' and '$currentModuleClass'."
            ))->forClass($currentModuleClass);
        }

        // Skip if the module is already resolved
        if ($this->isModuleLoaded($currentModuleClass, $resolvedModules)) {
            return $resolvedModules;
        }

        // Register the module as resolved
        $resolvedModules[$currentModuleClass] = $currentResolvedModule;

        // Resolve and load imported modules recursively
        foreach ($currentResolvedModule->getImports() as $importedModuleClass) {
            $this->loadModule($currentResolvedModule, $importedModuleClass, $resolvedModules);
        }

        return $resolvedModules;
    }

    /**
     * Checks if a module is already resolved and loaded.
     *
     * @param string $moduleClass The module class name to check.
     * @param array<string, ResolvedModule> $resolvedModules List of resolved modules.
     * @return bool True if the module is already resolved, false otherwise.
     */
    private function isModuleLoaded(string $moduleClass, array $resolvedModules): bool
    {
        return isset($resolvedModules[$moduleClass]);
    }

    /**
     * Checks for circular dependencies between modules.
     *
     * @param ResolvedModule $resolvedModule The module being resolved.
     * @param ResolvedModule $resolvedParentModule The parent module of the current module.
     * @return bool True if a circular dependency is detected, false otherwise.
     */
    private function hasCircularDependency(
        ResolvedModule $resolvedModule,
        ResolvedModule $resolvedParentModule
    ): bool {
        return in_array($resolvedModule->getClass(), $resolvedParentModule->getImportsAsClasses()) &&
            in_array($resolvedParentModule->getClass(), $resolvedModule->getImportsAsClasses());
    }
}
