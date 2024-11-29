<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Hookable\Hookable;

/**
 * Class HookComponent
 *
 * The `HookComponent` class represents a component associated with a hookable provider in the ModulesPress framework.
 * It extends from `ProviderComponent` and adds a specific reference to a hookable provider. This class holds the 
 * necessary reflection information about the provider and the hookable feature.
 *
 */
class HookComponent extends ProviderComponent
{
    /**
     * HookComponent constructor.
     *
     * Initializes the hook component with the resolved module, provider, reflection data for the provider class and method, 
     * and the hookable instance.
     *
     * @param ResolvedModule $resolvedModule The resolved module associated with this hook component.
     * @param Provider $provider The provider associated with this component.
     * @param ReflectionClass $providerClassReflection The reflection of the provider class.
     * @param ReflectionMethod $providerMethodReflection The reflection of the provider method.
     * @param Hookable $hookable The hookable instance associated with this component.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly Provider $provider,
        private readonly ReflectionClass $providerClassReflection,
        private readonly ReflectionMethod $providerMethodReflection,
        private readonly Hookable $hookable
    ) {
        parent::__construct($resolvedModule, $provider, $providerClassReflection, $providerMethodReflection);
    }

    /**
     * Get the hookable instance associated with this component.
     *
     * @return Hookable The hookable instance.
     */
    public function getHookable(): Hookable
    {
        return $this->hookable;
    }
}
