<?php

namespace ModulesPress\Foundation\Module\Attributes;

use Attribute;

/**
 * Class GlobalModule
 *
 * The `GlobalModule` attribute is used to mark a class as a global module within the system. 
 * Global modules are typically modules that provide core functionality or services which 
 * should be available throughout the entire plugin, regardless of where they are explicitly imported.
 * 
 * Applying this attribute to a module class will ensure that it is treated as a global module, 
 * making its services, providers, or other resources accessible globally to other modules, 
 * controllers, or services in the system without needing to explicitly import it.
 *
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class GlobalModule {}
