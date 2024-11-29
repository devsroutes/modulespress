<?php

namespace ModulesPress\Foundation\Module\Attributes;

use Attribute;
use ModulesPress\Common\Provider\Provider;
use ModulesPress\Foundation\Module\Contracts\DynamicModule;

/**
 * Class Module
 *
 * The `Module` attribute is used to define a module in the plugin. It is typically applied to a class
 * that represents a module. The module class can import other modules, register providers, and expose controllers,
 * entities, and exports for use in the system.
 *
 * The module system is based on the concept of importing dependencies, registering providers for services, 
 * defining controllers to handle routes, defining entities that represent data models, and exporting functionality
 * that can be used by other modules.
 *
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Module
{
    /**
     * Module constructor.
     *
     * @param string[]|DynamicModule[] $imports Modules to be imported, can be module classes or dynamic modules.
     * @param Provider[] $providers The providers to be registered within this module.
     * @param string[] $controllers The controllers associated with this module.
     * @param string[] $entities The entities associated with this module, typically data models.
     * @param string[] $exports The list of tokens of providers that are exported for other modules to use.
     */
    public function __construct(
        public array $imports,
        public array $providers,
        public array $controllers,
        public array $entities,
        public array $exports
    ) {}
}
