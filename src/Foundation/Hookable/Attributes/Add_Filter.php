<?php

namespace ModulesPress\Foundation\Hookable\Attributes;

use Attribute;
use ModulesPress\Foundation\Hookable\Hookable;

/**
 * Class Add_Filter
 *
 * The `Add_Filter` attribute is used to associate a method with a WordPress filter hook.
 * By applying this attribute to a method, it binds that method to the specified WordPress filter hook.
 * The method will be executed when the filter hook is triggered, and the method can modify data before returning it.
 * You can specify a priority to control the order in which the method is executed relative to other functions hooked to the same filter.
 *
 * This class extends the `Hookable` class, which provides the functionality to manage and register hook callbacks.
 * The `Add_Filter` attribute is applied to methods and used to define custom behavior within WordPress' filter hook system.
 *
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class Add_Filter extends Hookable
{
    /**
     * Add_Filter constructor.
     *
     * @param string $hook The name of the WordPress filter hook to bind to.
     * @param int $priority The priority of the hook. Default is 10. 
     *                      A lower value will result in the method being called earlier in the hook execution sequence.
     */
    public function __construct(
        private string $hook,
        private int $priority = 10,
    ) {
        parent::__construct($hook, $priority);
    }
}
