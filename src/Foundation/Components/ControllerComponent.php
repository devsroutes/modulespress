<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Http\Attributes\RestController;

/**
 * Class ControllerComponent
 *
 * The `ControllerComponent` class represents a component that is associated with a REST controller in the ModulesPress framework.
 * It holds references to the resolved module, the controller, and reflections of the class and method associated with the controller.
 * 
 * @package ModulesPress\Foundation\Components
 */
abstract class ControllerComponent
{
    /**
     * ControllerComponent constructor.
     *
     * @param ResolvedModule $resolvedModule The resolved module to which this controller belongs.
     * @param RestController $controller The controller attribute applied to the method.
     * @param ReflectionClass $classReflection Reflection of the controller class.
     * @param ReflectionMethod $methodReflection Reflection of the method that the controller handles.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly RestController $controller,
        private readonly ReflectionClass $classReflection,
        private readonly ReflectionMethod $methodReflection,
    ) {}

    /**
     * Get the RestController attribute applied to the method.
     *
     * @return RestController The controller attribute instance.
     */
    public function getController(): RestController
    {
        return $this->controller;
    }

    /**
     * Get the resolved module associated with this controller.
     *
     * @return ResolvedModule The resolved module.
     */
    public function getResolvedModule(): ResolvedModule
    {
        return $this->resolvedModule;
    }

    /**
     * Get the reflection of the controller class.
     *
     * @return ReflectionClass The reflection of the controller class.
     */
    public function getClassReflection(): ReflectionClass
    {
        return $this->classReflection;
    }

    /**
     * Get the reflection of the method associated with the controller.
     *
     * @return ReflectionMethod The reflection of the method.
     */
    public function getMethodReflection(): ReflectionMethod
    {
        return $this->methodReflection;
    }
}
