<?php

namespace ModulesPress\Foundation\DI\Attributes;

use Attribute;

/**
 * Class Inject
 *
 * The `Inject` class is an attribute used for dependency injection in the ModulesPress framework. This attribute can be
 * applied to constructor parameters to specify that the parameter should be injected with a dependency, identified by a
 * specific token.
 *
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Inject
{
    /**
     * Inject constructor.
     *
     * Initializes the `Inject` attribute with the given token, which identifies the dependency to be injected.
     *
     * @param string $token The token representing the dependency to be injected.
     */
    public function __construct(
        private readonly string $token
    ) {}

    /**
     * Get the token associated with this injection.
     *
     * @return string The token representing the dependency to be injected.
     */
    public function getToken(): string
    {
        return $this->token;
    }
}
