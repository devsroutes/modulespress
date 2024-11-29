<?php

namespace ModulesPress\Foundation\Entity\CPT;

/**
 * Field serves as a base class for fields associated with WordPress custom post types (CPT).
 * This class provides a mechanism for defining default values for fields, 
 * which can either be static values or dynamically generated via a callable.
 */
abstract class Field
{
    /**
     * The default value for the field.
     * It can be any type, including a callable that will be invoked to get the default value.
     *
     * @var mixed
     */
    public mixed $default;

    /**
     * Constructor to initialize the default value for the field.
     *
     * @param mixed $default The default value for the field, can be any type.
     */
    public function __construct(
         mixed $default = null,
    ) {
        $this->default = $default;
    }

    /**
     * Returns the default value of the field.
     * If the default value is a callable, it will invoke the callable and return its result.
     *
     * @return mixed The default value, or the result of the callable if the default is a callable.
     */
    public function getDefaultValue()
    {
        // Check if the default value is callable, and if so, invoke it.
        if (is_callable($this->default)) {
            return call_user_func($this->default);
        }

        // Otherwise, return the default value as-is.
        return $this->default;
    }
}
