<?php

namespace ModulesPress\Foundation\Http;

use ModulesPress\Foundation\Http\Contracts\PipeTransform;

/**
 * Abstract class RequestParameter
 * 
 * Represents the structure and behavior of request parameters within the framework.
 * Provides rules validation, casting, and transformation capabilities using pipes.
 */
abstract class RequestParameter
{
    /**
     * Constructor for RequestParameter.
     *
     * @param array $rules Array of validation rules to apply to the parameters.
     * @param bool $casting Whether type casting is enabled for the parameters.
     * @param array $pipes Array of pipes for parameter transformations. Each pipe
     *                     can either be an instance or a class-string of a PipeTransform.
     */
    public function __construct(
        private readonly array $rules,
        private readonly bool $casting,
        private readonly array $pipes,
    ) {}

    /**
     * Get the validation rules associated with the request parameters.
     *
     * @return array Array of validation rules.
     */
    public function getRules(): array
    {
        return $this->rules;
    }

    /**
     * Check if casting is enabled for the request parameters.
     *
     * @return bool True if casting is enabled, false otherwise.
     */
    public function isCastingEnable(): bool
    {
        return $this->casting;
    }

    /**
     * Get the pipes used for parameter transformations.
     *
     * @return PipeTransform[]|class-string<PipeTransform>[] Array of pipes, 
     *                                                       which can be instances or class-strings of PipeTransform.
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }
}
