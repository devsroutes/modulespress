<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\RequestParameter;

/**
 * Class Body
 *
 * This class represents an attribute used to define body parameters in HTTP requests.
 * It extends the `RequestParameter` class, allowing customization of rules, casting behavior, and pipes
 * that can be applied to the body parameter in the request.
 *
 * The attribute is applied to method parameters and binds them to specific body data
 * in the request, allowing additional validation, transformation, and processing.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class Body extends RequestParameter
{
    /**
     * The key associated with the body parameter.
     *
     * This key can be used to fetch the specific value from the request body.
     *
     * @var string
     */
    private string $key;

    /**
     * Constructor for the Body attribute.
     *
     * This constructor initializes the `Body` attribute with a specified key, rules, casting behavior,
     * and any pipes to be applied to the body parameter.
     *
     * @param string $key The key associated with the body parameter (defaults to an empty string).
     * @param array $rules An array of validation rules to be applied to the body parameter (defaults to an empty array).
     * @param bool $casting A flag indicating whether type casting should be enabled for the parameter (defaults to true).
     * @param array $pipes An array of pipes to apply to the body parameter (defaults to an empty array).
     */
    public function __construct(
        string $key = "",
        array $rules = [],
        bool $casting = true,
        array $pipes = []
    ) {
        $this->key = $key;
        parent::__construct($rules, $casting, $pipes);
    }

    /**
     * Get the key associated with the body parameter.
     *
     * The key is used to retrieve the specific value from the request body.
     *
     * @return string The key for the body parameter.
     */
    public function getKey(): string
    {
        return $this->key;
    }
}
