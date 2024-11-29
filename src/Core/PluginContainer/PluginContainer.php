<?php

namespace ModulesPress\Core\PluginContainer;

use DI\Container;
use DI\ContainerBuilder;
use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Core\Core;
use ModulesPress\Core\DiscoveryService\DiscoveryService;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Components\HookComponent;
use ModulesPress\Foundation\Components\RouteComponent;
use ModulesPress\Foundation\Components\ViewComposeComponent;
use ModulesPress\Foundation\Components\ViewDirectiveComponent;
use ModulesPress\Foundation\Exception\Contracts\ExceptionFilter;
use ModulesPress\Foundation\Guard\Contracts\CanActivate;
use ModulesPress\Foundation\Http\Contracts\Interceptor;
use ModulesPress\Foundation\Http\Contracts\PipeTransform;
use ReflectionClass;

/**
 * Class PluginContainer
 *
 * Handles module discovery, dependency injection, and registration of components like routes, hooks,
 * view composers, view directives, global interceptors, filters, guards, and pipes.
 */
final class PluginContainer
{
    private Container $container;

    /**
     * @var array<string, ResolvedModule> Resolved modules mapped by their keys.
     */
    private array $resolvedModules = [];

    /**
     * @var RouteComponent[] Registered route components.
     */
    private array $routes = [];

    /**
     * @var HookComponent[] Registered hook components.
     */
    private array $hooks = [];

    /**
     * @var ViewComposeComponent[] Registered view composer components.
     */
    private array $viewComposers = [];

    /**
     * @var ViewDirectiveComponent[] Registered view directive components.
     */
    private array $viewDirectives = [];

    /**
     * @param array<ResolvedModule, Interceptor|class-string<Interceptor>> $globalInterceptors Global interceptors.
     */
    private array $globalInterceptors = [];

    /**
     * @param array<ResolvedModule, ExceptionFilter|class-string<ExceptionFilter>> $globalFilters Global exception filters.
     */
    private array $globalFilters = [];

    /**
     * @param array<ResolvedModule, CanActivate|class-string<CanActivate>> $globalGuards Global guards.
     */
    private array $globalGuards = [];

    /**
     * @param array<ResolvedModule, PipeTransform|class-string<PipeTransform>> $globalPipes Global pipes.
     */
    private array $globalPipes = [];

    public function __construct(
        private readonly Core $core,
        private readonly string $appModuleClass,
        private readonly DiscoveryService $discoveryService
    ) {
        $this->container = $this->createDIContainer();
    }

    /**
     * Builds the dependency injection system and resolves all modules and their components.
     */
    public function buildDependencySystem()
    {
        $this->resolvedModules = $this->discoveryService->discoverModules($this->appModuleClass);

        foreach (
            $this->resolvedModules as $resolvedModule
        ) {

            foreach ($resolvedModule->getProviders() as $provider) {

                if ($provider->hasUsableClass()) {
                    $this->core->getResolver()->validateClassDependencies($resolvedModule, $provider->getUsableClass());
                    $this->set($provider->getProvidedToken(), $this->onClassRequest($resolvedModule, $provider->getUsableClass()));

                    $providerClassReflection = new ReflectionClass($provider->getUsableClass());
                    $methods = $providerClassReflection->getMethods();

                    foreach ($methods as $providerMethodReflection) {

                        if ($providerMethodReflection->getName() === '__construct') {
                            continue;
                        }

                        $this->discoveryService->discoverHookComponents(
                            $resolvedModule,
                            $provider,
                            $providerClassReflection,
                            $providerMethodReflection,
                            $this->hooks
                        );

                        $this->discoveryService->discoverViewComposeComponents(
                            $resolvedModule,
                            $provider,
                            $providerClassReflection,
                            $providerMethodReflection,
                            $this->viewComposers
                        );

                        $this->discoveryService->discoverViewDirectiveComponents(
                            $resolvedModule,
                            $provider,
                            $providerClassReflection,
                            $providerMethodReflection,
                            $this->viewDirectives
                        );
                    }
                } else if ($provider->hasUsableFactory()) {
                    $this->core->getResolver()->validateFactoryDependencies($resolvedModule, $provider->getUsableFactory());
                    $this->set($provider->getProvidedToken(), $this->onFactoryRequest($resolvedModule, $provider->getUsableFactory()));
                } else {
                    $this->set($provider->getProvidedToken(), $provider->getUsableValue());
                }
            }

            foreach ($resolvedModule->getControllers() as $controllerClassName) {
                $this->core->getResolver()->validateClassDependencies($resolvedModule, $controllerClassName);
                $this->set($controllerClassName, $this->onClassRequest($resolvedModule, $controllerClassName));

                $controllerClassReflection = new ReflectionClass($controllerClassName);
                $methods = $controllerClassReflection->getMethods();
                $controllers = AttributesScanner::scanRestControllers($controllerClassReflection);
                if (empty($controllers)) {
                    continue;
                }
                $controller = $controllers[0];

                foreach ($methods as $controllerMethodReflection) {
                    if ($controllerMethodReflection->getName() === '__construct') {
                        continue;
                    }
                    $this->discoveryService->discoverRouteComponents(
                        $resolvedModule,
                        $controller,
                        $controllerClassReflection,
                        $controllerMethodReflection,
                        $this->routes,
                    );
                }
            }

            call_user_func(
                [$resolvedModule->getInstance(), 'middlewares'],
                $this->core->getHttp()->getMiddlewareConsumer()
            );

            /**
             * @var CanActivate[]|class-string<CanActivate>[] $guards
             */
            $guards = call_user_func([$resolvedModule->getInstance(), 'pluginGuards']);
            foreach ($guards as $guard) {
                array_push(
                    $this->globalGuards,
                    [$resolvedModule, $guard]
                );
            }

            /**
             * @var Interceptor[]|class-string<Interceptor>[] $interceptors
             */
            $interceptors = call_user_func([$resolvedModule->getInstance(), 'pluginInterceptors']);
            foreach ($interceptors as $interceptor) {
                array_push(
                    $this->globalInterceptors,
                    [$resolvedModule, $interceptor]
                );
            }

            /**
             * @var Interceptor[]|class-string<Interceptor>[] $interceptors
             */
            $pipes = call_user_func([$resolvedModule->getInstance(), 'pluginPipes']);
            foreach ($pipes as $pipe) {
                array_push(
                    $this->globalPipes,
                    [$resolvedModule, $pipe]
                );
            }

            /**
             * @var ExceptionFilter[]|class-string<ExceptionFilter>[] $filters
             */
            $filters = call_user_func([$resolvedModule->getInstance(), 'pluginFilters']);
            foreach ($filters as $filter) {
                array_push(
                    $this->globalFilters,
                    [$resolvedModule, $filter]
                );
            }
        }
    }

    /**
     * When a class is requested, it is resolved using the resolver.
     *
     * @param ResolvedModule $resolvedModule The resolved module.
     * @param string $classDefinition The class definition.
     *
     */
    private function onClassRequest(ResolvedModule $resolvedModule, string $classDefinition)
    {
        return function (
            // ContainerInterface $container,
            // FactoryDefinition $factory_definitions
        )  use ($resolvedModule, $classDefinition) {
            return $this->core->getResolver()->resolve($resolvedModule, $classDefinition);
        };
    }

    /**
     * When a factory is requested, it is resolved using the resolver.
     *
     * @param ResolvedModule $resolvedModule The resolved module.
     * @param callable $factory The factory.
     *
     */
    private function onFactoryRequest(ResolvedModule $resolvedModule, callable $factory)
    {
        return function () use ($resolvedModule, $factory) {
            return $this->core->getResolver()->resolveFactory($resolvedModule, $factory);
        };
    }

    /**
     * Creates a plugin dependency injection container using PHP-DI.
     *
     * @return Container The created container.
     */
    private function createDIContainer(): Container
    {
        $builder = new ContainerBuilder();
        $builder->useAutowiring(false);
        return $builder->build();
    }

    /**
     * Sets a value in the dependency container.
     *
     * @param string $key The key.
     * @param mixed $value The value.
     */
    public function set(string $key, mixed $value): void
    {
        $this->container->set($key, $value);
    }

    /**
     * Gets a value from the dependency container. Cached values are returned if resolved.
     *
     * @param string $key The key.
     * @return mixed The value.
     */
    public function get(string $key): mixed
    {
        return $this->container->get($key);
    }

    /**
     * Checks if a value exists in the dependency container.
     *
     * @param string $key The key.
     * @return bool True if the value exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return $this->container->has($key);
    }

    /**
     * Retrieves a new value from the dependency container everytime.
     *
     * @param string $key The key.
     * @return mixed The value.
     */
    public function make(string $key): mixed
    {
        return $this->container->make($key);
    }

    /**
     * Returns all resolved modules.
     *
     * @return array<string, ResolvedModule> Array of resolved modules.
     */
    public function getResolvedModules(): array
    {
        return $this->resolvedModules;
    }

    /**
     * Fetches a resolved module by its unique key.
     *
     * @param string $key The unique identifier for the module.
     * @return ResolvedModule|null The resolved module if found, or null if not found.
     */
    public function getResolvedModuleByKey(string $key): ResolvedModule | null
    {
        return $this->resolvedModules[$key] ?? null;
    }

    /**
     * Retrieves a resolved module by a dependency key it provides.
     *
     * @param string $key The dependency key provided by the module.
     * @return ResolvedModule|null The resolved module if the key is found, or null if not found.
     */
    public function getResolvedModuleByDependencyKey(string $key): ResolvedModule | null
    {
        foreach ($this->resolvedModules as $resolvedModule) {
            if (in_array($key, $resolvedModule->getProvidedTokens())) {
                return $resolvedModule;
            }
        }
        return null;
    }

    /**
     * Retrieves the provider for a specific dependency key.
     *
     * @param string $key The dependency key for which to find the provider.
     * @return Provider|null The provider associated with the key, or null if not found.
     */
    public function getProviderByDependencyKey(string $key): Provider | null
    {
        foreach ($this->resolvedModules as $resolvedModule) {
            foreach ($resolvedModule->getProviders() as $provider) {
                if ($provider->getProvidedToken() === $key) {
                    return $provider;
                }
            }
        }
        return null;
    }

    /**
     * Retrieves all registered route components.
     *
     * @return RouteComponent[] Array of route components.
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }

    /**
     * Retrieves all registered hook components.
     *
     * @return HookComponent[] Array of hook components.
     */
    public function getHooks(): array
    {
        return $this->hooks;
    }

    /**
     * Retrieves all registered view composers.
     *
     * @return ViewComposeComponent[] Array of view composer components.
     */
    public function getViewComposers(): array
    {
        return $this->viewComposers;
    }

    /**
     * Retrieves all registered view directives.
     *
     * @return ViewDirectiveComponent[] Array of view directive components.
     */
    public function getViewDirectives(): array
    {
        return $this->viewDirectives;
    }

    /**
     * Retrieves all globally registered interceptors.
     *
     * @return Interceptor[] Array of resolved global interceptors.
     */
    public function getGlobalInterceptors(): array
    {
        return array_map(function ($arg) {
            return $this->core->getResolver()->resolve($arg[0], $arg[1], true);
        }, $this->globalInterceptors);
    }

    /**
     * Retrieves all globally registered exception filters.
     *
     * @return ExceptionFilter[] Array of resolved global exception filters.
     */
    public function getGlobalFilters(): array
    {
        return array_map(function ($arg) {
            return $this->core->getResolver()->resolve($arg[0], $arg[1], true);
        }, $this->globalFilters);
    }

    /**
     * Retrieves all globally registered guards.
     *
     * @return CanActivate[] Array of resolved global guards.
     */
    public function getGlobalGuards(): array
    {
        return array_map(function ($arg) {
            return $this->core->getResolver()->resolve($arg[0], $arg[1], true);
        }, $this->globalGuards);
    }

    /**
     * Retrieves all globally registered pipes.
     *
     * @return PipeTransform[] Array of resolved global pipes.
     */
    public function getGlobalPipes(): array
    {
        return array_map(function ($arg) {
            return $this->core->getResolver()->resolve($arg[0], $arg[1], true);
        }, $this->globalPipes);
    }
}
