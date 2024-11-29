<?php

namespace ModulesPress\Core\DiscoveryService;

use ModulesPress\Foundation\Components\HookComponent;
use ModulesPress\Foundation\Components\RouteComponent;
use ModulesPress\Foundation\Components\ViewComposeComponent;
use ModulesPress\Foundation\Components\ViewDirectiveComponent;

/**
 * Class DiscoveryResult
 *
 * Represents the result of a discovery process for identifying various components within the framework.
 * The discovery process identifies routes, hooks, view composers, and view directives registered by modules.
 *
 * @package ModulesPress\Core\DiscoveryService
 */
class DiscoveryResult
{
    /**
     * DiscoveryResult constructor.
     *
     * @param RouteComponent[] $routesComponents       An array of discovered route components.
     * @param HookComponent[] $hooksComponents         An array of discovered hook components.
     * @param ViewComposeComponent[] $viewComposersComponents An array of discovered view composer components.
     * @param ViewDirectiveComponent[] $viewDirectivesComponents An array of discovered view directive components.
     */
    public function __construct(
        private readonly array $routesComponents,
        private readonly array $hooksComponents,
        private readonly array $viewComposersComponents,
        private readonly array $viewDirectivesComponents
    ) {}

    /**
     * Get the discovered route components.
     *
     * @return RouteComponent[] Array of RouteComponent instances.
     */
    public function getRoutesComponents(): array
    {
        return $this->routesComponents;
    }

    /**
     * Get the discovered hook components.
     *
     * @return HookComponent[] Array of HookComponent instances.
     */
    public function getHooksComponents(): array
    {
        return $this->hooksComponents;
    }

    /**
     * Get the discovered view composer components.
     *
     * @return ViewComposeComponent[] Array of ViewComposeComponent instances.
     */
    public function getViewComposersComponents(): array
    {
        return $this->viewComposersComponents;
    }

    /**
     * Get the discovered view directive components.
     *
     * @return ViewDirectiveComponent[] Array of ViewDirectiveComponent instances.
     */
    public function getViewDirectivesComponents(): array
    {
        return $this->viewDirectivesComponents;
    }
}
