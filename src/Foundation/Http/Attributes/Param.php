<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\RequestParameter;

/**
 * Class Param
 *
 * This class represents an attribute used to define route parameters in HTTP requests.
 * It extends the `RequestParameter` class, enabling the extraction of dynamic parameters 
 * from the URL path (e.g., `:id` in a route like `/user/:id`).
 *
 * The attribute is applied to method parameters and binds them to specific route parameters 
 * in the URL, enabling additional validation, transformation, and processing.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Param extends RequestParameter
{
    /**
     * The key associated with the parameter.
     *
     * This key corresponds to the dynamic segment in the URL (e.g., `id` in `/user/:id`).
     *
     * @var string
     */
    private string $key;

    /**
     * Constructor for the Param attribute.
     *
     * This constructor initializes the `Param` attribute with a specified key, validation rules, 
     * type casting behavior, and any pipes to be applied to the parameter.
     *
     * @param string $key The key associated with the route parameter (e.g., `id` in `/user/:id`).
     * @param array $rules An array of validation rules to be applied to the parameter (defaults to an empty array).
     * @param bool $casting A flag indicating whether type casting should be enabled for the parameter (defaults to true).
     * @param array $pipes An array of pipes to apply to the parameter (defaults to an empty array).
     */
    public function __construct(
        string $key,
        array $rules = [],
        bool $casting = true,
        array $pipes = []
    ) {
        $this->key = $key;
        parent::__construct($rules, $casting, $pipes);
    }

    /**
     * Get the key associated with the route parameter.
     *
     * This key is used to retrieve the value from the URL path parameters (e.g., `id` in `/user/:id`).
     *
     * @return string The key for the route parameter.
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
