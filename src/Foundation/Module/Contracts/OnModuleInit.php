<?php 

namespace ModulesPress\Foundation\Module\Contracts;

use ModulesPress\Core\Resolver\ResolvedModule;

/**
 * Interface OnModuleInit
 *
 * The `OnModuleInit` interface defines a contract for classes that need to perform
 * initialization logic when a module is resolved and loaded by the system. Classes
 * implementing this interface will have the opportunity to execute specific operations
 * during the module initialization phase.
 *
 */
interface OnModuleInit
{
    /**
     * Execute initialization logic for a module.
     *
     * This method is called during the initialization phase of a module. It receives
     * an instance of `ResolvedModule`, which contains information and dependencies
     * related to the resolved module.
     *
     * @param ResolvedModule $module The resolved module instance containing module-related details.
     * 
     * @return void
     */
    public function onModuleInit(ResolvedModule $module): void;
}
