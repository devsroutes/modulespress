<?php

namespace ModulesPress\Foundation\Module\Contracts;

use ModulesPress\Foundation\Module\Attributes\Module;

/**
 * Interface DynamicModule
 *
 * The `DynamicModule` interface defines a contract for dynamically registering
 * a module. Classes implementing this interface are responsible for returning
 * a configured `Module` attribute instance during runtime.
 *
 */
interface DynamicModule
{
    /**
     * Dynamically register a module.
     *
     * This method should return a `Module` instance, which represents the
     * configuration and metadata for the dynamically created module.
     *
     * @return Module The configured module instance.
     */
    public function register(): Module;
}
