<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\Resolver\ResolvedModule;

/**
 * Class ProviderComponent
 *
 * The `ProviderComponent` class represents a component associated with a provider in the ModulesPress framework.
 * It holds references to the resolved module, provider, and the reflection information for the class and method.
 * This class provides accessors to the various components that can be used for processing hooks or other provider-related tasks.
 *
 */
abstract class ProviderComponent
{
    /**
     * ProviderComponent constructor.
     *
     * Initializes the component with the resolved module, provider, and reflection data for the class and method.
     *
     * @param ResolvedModule $resolvedModule The resolved module associated with this provider component.
     * @param Provider $provider The provider associated with this component.
     * @param ReflectionClass $classReflection The reflection of the provider class.
     * @param ReflectionMethod $methodReflection The reflection of the provider method.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly Provider $provider,
        private readonly ReflectionClass $classReflection,
        private readonly ReflectionMethod $methodReflection,
    ) {}

    /**
     * Get the provider associated with this component.
     *
     * @return Provider The provider instance.
     */
    public function getProvider(): Provider
    {
        return $this->provider;
    }

    /**
     * Get the resolved module associated with this component.
     *
     * @return ResolvedModule The resolved module instance.
     */
    public function getResolvedModule(): ResolvedModule
    {
        return $this->resolvedModule;
    }

    /**
     * Get the class reflection associated with this provider component.
     *
     * @return ReflectionClass The reflection instance for the provider class.
     */
    public function getClassReflection(): ReflectionClass
    {
        return $this->classReflection;
    }

    /**
     * Get the method reflection associated with this provider component.
     *
     * @return ReflectionMethod The reflection instance for the provider method.
     */
    public function getMethodReflection(): ReflectionMethod
    {
        return $this->methodReflection;
    }
}
