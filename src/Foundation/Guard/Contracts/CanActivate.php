<?php

namespace ModulesPress\Foundation\Guard\Contracts;

use ModulesPress\Core\ExecutionContext\ExecutionContext;

/**
 * Interface CanActivate
 *
 * The `CanActivate` interface defines the structure for guard classes that control access to methods or routes.
 * Implementing this interface allows a class to be used as a guard, determining whether a method or route should be accessible
 * based on the context and logic defined in the `canActivate` method.
 *
 * Guards implementing this interface are used by the `UseGuards` attribute to check whether a method or action can be executed.
 * The `canActivate` method should return `true` to allow access and `false` to block access, based on the execution context.
 * 
 * The `CanActivate` interface can also define the check structure and can be used with `UseChecks` attribute.
 *
 */
interface CanActivate
{
    /**
     * Determines if the current method or route can be accessed.
     *
     * The `canActivate` method receives an `ExecutionContext` object, which provides information about the current execution
     * environment, such as the hook amd rest context.
     *
     * @param ExecutionContext $executionContext The context in which the method or route is being executed.
     * 
     * @return bool `true` to allow access, `false` to deny access.
     */
    public function canActivate(ExecutionContext $executionContext): bool;
}
