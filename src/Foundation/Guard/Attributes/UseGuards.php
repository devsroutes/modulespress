<?php

namespace ModulesPress\Foundation\Guard\Attributes;

use Attribute;
use ModulesPress\Foundation\Guard\Contracts\CanActivate;

/**
 * Class UseGuards
 *
 * The `UseGuards` attribute is used to apply guards to methods or classes within the framework.
 * Guards are used for controlling access to specific methods or endpoints based on certain conditions,
 * such as user roles or authentication status.
 *
 * This attribute allows the assignment of one or more guards to a method or class. Guards can either 
 * be specified as strings (names of predefined guard classes) or as instances of classes implementing
 * the `CanActivate` interface.
 *
 * Guards are evaluated before executing the associated method, and if any guard returns false or prevents
 * access, the method execution will be blocked.
 *
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class UseGuards
{
    /**
     * @var string[]|CanActivate[] The list of guards applied to the method or class.
     * Guards can be either guard names (strings) or guard instances implementing CanActivate.
     */
    private array $guards = [];

    /**
     * UseGuards constructor.
     * 
     * @param string|CanActivate ...$guards The guards to apply to the method or class. Can be guard class names or guard instances.
     */
    public function __construct(...$guards)
    {
        $this->guards = $guards;
    }

    /**
     * Get the guards applied to the method or class.
     *
     * @return string[]|CanActivate[] The list of guards.
     */
    public function getGuards(): array
    {
        return $this->guards;
    }
}
