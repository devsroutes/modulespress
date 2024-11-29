<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Common\Provider\Provider;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\View\Attributes\ViewDirective;

/**
 * Class ViewDirectiveComponent
 *
 * The `ViewDirectiveComponent` class represents a component that handles the `ViewDirective` functionality in the 
 * ModulesPress framework. It extends `ProviderComponent` and stores a reference to a `ViewDirective` attribute 
 * as well as reflection data related to the provider class and method.
 *
 */
class ViewDirectiveComponent extends ProviderComponent
{
    /**
     * ViewDirectiveComponent constructor.
     *
     * Initializes the view directive component with the resolved module, provider, reflection data for the provider class 
     * and method, and the `ViewDirective` attribute instance.
     *
     * @param ResolvedModule $resolvedModule The resolved module associated with this component.
     * @param Provider $provider The provider associated with the `ViewDirective` functionality.
     * @param ReflectionClass $providerClassReflection The reflection of the provider class.
     * @param ReflectionMethod $providerMethodReflection The reflection of the provider method.
     * @param ViewDirective $viewDirective The `ViewDirective` attribute instance associated with this component.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly Provider $provider,
        private readonly ReflectionClass $providerClassReflection,
        private readonly ReflectionMethod $providerMethodReflection,
        private readonly ViewDirective $viewDirective
    ) {
        parent::__construct($resolvedModule, $provider, $providerClassReflection, $providerMethodReflection);
    }

    /**
     * Get the `ViewDirective` instance associated with this component.
     *
     * @return ViewDirective The `ViewDirective` attribute instance.
     */
    public function getViewDirective(): ViewDirective
    {
        return $this->viewDirective;
    }
}
