<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\RequestParameter;

/**
 * Class Query
 *
 * This class represents an attribute used to define query parameters in HTTP requests.
 * It extends the `RequestParameter` class, allowing customization of validation rules, casting behavior, and pipes
 * that can be applied to query parameters in the request URL.
 *
 * The attribute is applied to method parameters and binds them to specific query parameters in the URL,
 * enabling additional validation, transformation, and processing.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Query extends RequestParameter
{
    /**
     * The key associated with the query parameter.
     *
     * This key is used to retrieve the specific value from the query parameters in the URL.
     *
     * @var string
     */
    private string $key;

    /**
     * Constructor for the Query attribute.
     *
     * This constructor initializes the `Query` attribute with a specified key, validation rules, casting behavior,
     * and any pipes to be applied to the query parameter.
     *
     * @param string $key The key associated with the query parameter. This key is used to retrieve the value from the query string.
     * @param array $rules An array of validation rules to be applied to the query parameter (defaults to an empty array).
     * @param bool $casting A flag indicating whether type casting should be enabled for the query parameter (defaults to true).
     * @param array $pipes An array of pipes to apply to the query parameter (defaults to an empty array).
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
     * Get the key associated with the query parameter.
     *
     * The key is used to retrieve the specific value from the query string in the URL.
     *
     * @return string The key for the query parameter.
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
