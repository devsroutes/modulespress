<?php

namespace ModulesPress\Core\ExecutionContext;

use ModulesPress\Foundation\Hookable\Hookable;

/**
 * The HookContext class represents the context of an individual hook execution, 
 * holding details about the hookable object, the reflection of the class and method 
 * associated with the hook, and the arguments passed to the hook.
 */
final class HookContext
{
    public function __construct(
        private readonly Hookable $type,
        private readonly \ReflectionClass $classReflection,
        private readonly \ReflectionMethod $methodReflection,
        private readonly array $args,
    ) {}

    /**
     * Gets the hookable object associated with this hook context.
     *
     * @return Hookable The hookable object.
     */
    public function getHookable(): Hookable
    {
        return $this->type;
    }

    /**
     * Gets the class reflection for the hook handler class.
     *
     * @return \ReflectionClass The reflection of the class.
     */
    public function getClassReflection(): \ReflectionClass
    {
        return $this->classReflection;
    }

    /**
     * Gets the method reflection for the hook handler method.
     *
     * @return \ReflectionMethod The reflection of the method.
     */
    public function getMethodReflection(): \ReflectionMethod
    {
        return $this->methodReflection;
    }

    /**
     * Gets the arguments passed to the hook handler.
     *
     * @return array The arguments passed to the hook method.
     */
    public function getArgs(): array
    {
        return $this->args;
    }
}
