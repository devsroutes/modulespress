<?php

namespace ModulesPress\Foundation\Hookable;

use Attribute;

/**
 * Class Hookable
 *
 * The `Hookable` class serves as a base for defining hookable methods that can be attached to WordPress actions or filters.
 * It is designed to be extended by specific hook-related attributes like `Add_Action` and `Add_Filter`, 
 * which are used to register methods to specific WordPress hooks.
 *
 * This abstract class encapsulates the hook name and priority, allowing these values to be retrieved for further processing.
 * By using this class, methods can easily be tied to WordPress hooks with a defined priority for execution order.
 *
 */
#[Attribute(Attribute::TARGET_METHOD)]
abstract class Hookable
{
    /**
     * Hookable constructor.
     *
     * @param string $hook The name of the WordPress hook (action or filter) to associate the method with.
     * @param int $priority The priority for the hook. Default is a high priority (10).
     *                      Lower values execute earlier, higher values later.
     */
    public function __construct(
        private string $hook,
        private int $priority,
    ) {}

    /**
     * Get the name of the hook.
     *
     * @return string The name of the WordPress hook.
     */
    public function getHookName(): string
    {
        return $this->hook;
    }

    /**
     * Get the priority of the hook.
     *
     * @return int The priority of the hook. A lower number indicates earlier execution.
     */
    public function getPriority(): int
    {
        return $this->priority;
    }
}
