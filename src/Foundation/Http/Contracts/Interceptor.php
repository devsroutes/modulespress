<?php

namespace ModulesPress\Foundation\Http\Contracts;

use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\Http\CallHandler;

/**
 * Interface Interceptor
 *
 * This interface defines a contract for interceptors that can modify the request/response flow.
 * Interceptors are typically used for cross-cutting concerns like logging, authentication, caching,
 * or modifying the execution context before or after the main request handler processes the request.
 */
interface Interceptor
{
    /**
     * Intercepts the execution of the request/response flow.
     *
     * This method provides the opportunity for the interceptor to modify the `ExecutionContext`,
     * perform pre- or post-processing, or decide to terminate the process early. It allows you to
     * apply logic before or after the main handler is executed.
     *
     * @param ExecutionContext $executionContext The context for the current request/response execution.
     *                                            This may contain details such as input parameters, user context, etc.
     * @param CallHandler $next The handler to be invoked if the interceptor allows the flow to continue.
     *                           This represents the continuation of the request/response cycle.
     *
     * @return mixed The result of the interception. This could be the modified result from the handler,
     *               or a completely new result if the interceptor decides to alter or halt the execution flow.
     */
    public function intercept(ExecutionContext $executionContext, CallHandler $next): mixed;
}
