<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\View\Attributes\ViewCompose;

/**
 * Class ViewComposeComponent
 *
 * The `ViewComposeComponent` class represents a component that handles the `ViewCompose` functionality in the 
 * ModulesPress framework. It extends `ProviderComponent` and stores a reference to a `ViewCompose` attribute 
 * as well as reflection data related to the provider class and method.
 *
 */
class ViewComposeComponent extends ProviderComponent
{
    /**
     * ViewComposeComponent constructor.
     *
     * Initializes the view compose component with the resolved module, provider, reflection data for the provider class 
     * and method, and the `ViewCompose` attribute instance.
     *
     * @param ResolvedModule $resolvedModule The resolved module associated with this component.
     * @param Provider $provider The provider associated with the `ViewCompose` functionality.
     * @param ReflectionClass $providerClassReflection The reflection of the provider class.
     * @param ReflectionMethod $providerMethodReflection The reflection of the provider method.
     * @param ViewCompose $viewCompose The `ViewCompose` attribute instance associated with this component.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly Provider $provider,
        private readonly ReflectionClass $providerClassReflection,
        private readonly ReflectionMethod $providerMethodReflection,
        private readonly ViewCompose $viewCompose
    ) {
        parent::__construct($resolvedModule, $provider, $providerClassReflection, $providerMethodReflection);
    }

    /**
     * Get the `ViewCompose` instance associated with this component.
     *
     * @return ViewCompose The `ViewCompose` attribute instance.
     */
    public function getViewCompose(): ViewCompose
    {
        return $this->viewCompose;
    }
}
