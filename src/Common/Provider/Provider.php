<?php

namespace ModulesPress\Common\Provider;

use ModulesPress\Foundation\DI\Enums\Scope;

/**
 * Represents a provider definition for dependency injection.
 * A provider defines how a dependency is resolved, including class-based, value-based, or factory-based approaches.
 */
final class Provider
{
    /**
     * @param string $provide The token used to identify the provided dependency.
     * @param string $useClass The class to be instantiated (optional, defaults to an empty string).
     * @param mixed $useValue A predefined value to be returned (optional, defaults to an empty string).
     * @param array $useFactory A factory definition for creating the dependency (optional, defaults to an empty array).
     * @param string $scope The scope of the provider, e.g., singleton or transient (default: Scope::SINGLETON).
     */
    public function __construct(
        private readonly string $provide,
        private readonly string $useClass = "",
        private readonly mixed $useValue = "",
        private readonly array $useFactory = [],
        private readonly string $scope = Scope::SINGLETON
    ) {}

    /**
     * Retrieves the token identifying the dependency this provider handles.
     *
     * @return string The token associated with the dependency.
     */
    public function getProvidedToken(): string
    {
        return $this->provide;
    }

    /**
     * Checks if this provider specifies a usable class for instantiation.
     *
     * @return bool True if a class is specified, false otherwise.
     */
    public function hasUsableClass(): bool
    {
        return !empty($this->useClass);
    }

    /**
     * Retrieves the class name specified for this provider.
     *
     * @return string The class name to be used for resolving the dependency.
     */
    public function getUsableClass(): string
    {
        return $this->useClass;
    }

    /**
     * Checks if this provider specifies a usable value for resolution.
     *
     * @return bool True if a value is specified, false otherwise.
     */
    public function hasUsableValue(): bool
    {
        return !empty($this->useValue);
    }

    /**
     * Retrieves the predefined value specified for this provider.
     *
     * @return mixed The value to be used for resolving the dependency.
     */
    public function getUsableValue(): mixed
    {
        return $this->useValue;
    }

    /**
     * Checks if this provider specifies a usable factory for creating the dependency.
     *
     * @return bool True if a factory is specified, false otherwise.
     */
    public function hasUsableFactory(): bool
    {
        return !empty($this->useFactory);
    }

    /**
     * Retrieves the factory definition specified for this provider.
     *
     * @return array The factory definition for resolving the dependency.
     */
    public function getUsableFactory(): array
    {
        return $this->useFactory;
    }

    /**
     * Retrieves the scope of this provider.
     *
     * @return string The scope of the provider, e.g., singleton or transient.
     */
    public function getScope(): string
    {
        return $this->scope;
    }
}
