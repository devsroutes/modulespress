<?php

namespace ModulesPress\Foundation\Exception\Attributes;

use Attribute;

/**
 * Class UseExceptionFilter
 *
 * The `UseExceptionFilter` attribute is used to mark methods or classes that should apply specific exception
 * filters to handle exceptions during execution. The exception filters provided in this attribute will be 
 * used to process exceptions thrown within the method or class.
 *
 * This allows for customized exception handling behavior by using predefined filters.
 *
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class UseExceptionFilter
{
    /**
     * List of exception filters to be applied.
     *
     * @var array
     */
    private array $filters = [];

    /**
     * UseExceptionFilter constructor.
     *
     * @param string ...$filters A list of exception filter classes that will be applied to the method or class.
     */
    public function __construct(...$filters)
    {
        $this->filters = $filters;
    }

    /**
     * Get the list of filters applied to the method or class.
     *
     * @return array List of exception filters.
     */
    public function getFilters(): array
    {
        return $this->filters;
    }
}
