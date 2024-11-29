<?php

namespace ModulesPress\Core\ExceptionHandler;

use ModulesPress\Foundation\Exception\Contracts\ExceptionFilter;
use ModulesPress\Core\Resolver\ResolvedModule;

/**
 * The `ResolvedFilter` class is a container for an exception filter, including metadata 
 * that associates it with a specific key and a resolved module.
 * 
 * This class is used in the exception handling process to associate filters with 
 * specific exceptions and modules, allowing for flexible and dynamic filtering of 
 * exceptions based on the context and module resolution.
 */
final class ResolvedFilter
{
    /**
     * The unique key for the resolved filter, typically a combination of class and method.
     *
     * @var string
     */
    private readonly string $key;

    /**
     * The exception filter, either a class name or an instance implementing `ExceptionFilter`.
     *
     * @var string | ExceptionFilter
     */
    private readonly string | ExceptionFilter $filter;

    /**
     * The resolved module that this filter is associated with.
     *
     * @var ResolvedModule
     */
    private readonly ResolvedModule $resolvedModule;

    /**
     * Constructor to initialize the `ResolvedFilter` with the necessary parameters.
     *
     * @param string $key The unique key identifying the filter.
     * @param string|ExceptionFilter $filter The exception filter to be applied.
     * @param ResolvedModule $resolvedModule The resolved module to which this filter belongs.
     */
    public function __construct(
        string $key,
        string | ExceptionFilter $filter,
        ResolvedModule $resolvedModule,
    ) {
        $this->key = $key;
        $this->filter = $filter;
        $this->resolvedModule = $resolvedModule;
    }

    /**
     * Returns the key identifying this resolved filter.
     *
     * @return string The key.
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Returns the filter associated with this resolved filter, either as a string or an `ExceptionFilter` instance.
     *
     * @return string | ExceptionFilter The filter.
     */
    public function getFilter(): string | ExceptionFilter
    {
        return $this->filter;
    }

    /**
     * Returns the resolved module associated with this filter.
     *
     * @return ResolvedModule The resolved module.
     */
    public function getResolvedModule(): ResolvedModule
    {
        return $this->resolvedModule;
    }
}
