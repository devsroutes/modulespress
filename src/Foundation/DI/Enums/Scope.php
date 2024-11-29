<?php

namespace ModulesPress\Foundation\DI\Enums;

/**
 * Enum Scope
 *
 * The `Scope` enum defines the different lifetimes (scopes) that a dependency can have in the dependency injection container.
 * This helps manage how long an object is kept in the container and how it is reused.
 *
 * There are two primary scopes:
 * 
 * 1. **SINGLETON**: A single instance of the dependency is created and shared throughout the WP request's lifecycle.
 *    This ensures that only one instance of the dependency exists, and it is reused wherever needed.
 *    
 * 2. **TRANSIENT**: A new instance of the dependency is created each time it is requested. This scope is useful when the dependency should not be shared across different components.
 *
 */
enum Scope: string
{
    /**
     * SINGLETON scope ensures that the same instance of the dependency is shared across the plugin.
     */
    const SINGLETON = 'MP\SINGLETON';

    /**
     * TRANSIENT scope ensures that a new instance of the dependency is created every time it is requested.
     */
    const TRANSIENT = 'MP\TRANSIENT';
}
