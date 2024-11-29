<?php

namespace ModulesPress\Foundation\Module;

use ModulesPress\Core\Http\MiddlewareConsumer;
use ModulesPress\Foundation\Exception\Contracts\ExceptionFilter;
use ModulesPress\Foundation\Guard\Contracts\CanActivate;
use ModulesPress\Foundation\Http\Contracts\Interceptor;
use ModulesPress\Foundation\Http\Contracts\PipeTransform;

/**
 * Abstract class ModulesPressModule
 *
 * Represents the base class for defining modules in the ModulesPress framework.
 * Provides hooks for globally registering middlewares, interceptors, guards,
 * pipes, and exception filters for the entire plugin.
 *
 */
abstract class ModulesPressModule
{
    /**
     * Register global middlewares for the plugin.
     *
     * This method is called to define middleware behavior for the module. The
     * `MiddlewareConsumer` allows configuration of route-level or module-level
     * middlewares.
     *
     * @param MiddlewareConsumer $consumer The middleware configuration consumer.
     */
    public function middlewares(MiddlewareConsumer $consumer): void {}

    /**
     * Register global interceptors for the plugin.
     *
     * Interceptors are used for modifying or logging requests/responses globally.
     *
     * @return Interceptor[]|class-string<Interceptor>[] 
     * An array of instantiated interceptors or their class names.
     */
    public function pluginInterceptors(): array { return []; }

    /**
     * Register global exception filters for the plugin.
     *
     * Exception filters define how specific exceptions should be handled globally,
     * providing a mechanism for custom error responses.
     *
     * @return ExceptionFilter[]|class-string<ExceptionFilter>[] 
     * An array of instantiated exception filters or their class names.
     */
    public function pluginFilters(): array { return []; }

    /**
     * Register global guards for the plugin.
     *
     * Guards are responsible for determining whether a request is allowed to proceed.
     *
     * @return CanActivate[]|class-string<CanActivate>[] 
     * An array of instantiated guard classes or their class names.
     */
    public function pluginGuards(): array { return []; }

    /**
     * Register global pipes for the plugin.
     *
     * Pipes are used to transform or validate request/response data globally.
     *
     * @return PipeTransform[]|class-string<PipeTransform>[] 
     * An array of instantiated pipes or their class names.
     */
    public function pluginPipes(): array { return []; }
}
