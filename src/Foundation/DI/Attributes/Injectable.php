<?php

namespace ModulesPress\Foundation\DI\Attributes;

use Attribute;

/**
 * Class Injectable
 *
 * The `Injectable` class is an attribute used to mark a class that requires dependency injection (DI).
 * 
 * When this attribute is applied to a class, the DI container will automatically resolve and inject the required
 * dependencies into the class when it is instantiated.
 * 
 * This is essential for classes that need dependencies to function, and ensures that dependencies are properly
 * managed by the framework's DI container.
 *
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class Injectable {}
