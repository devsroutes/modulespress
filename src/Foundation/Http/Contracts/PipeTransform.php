<?php

namespace ModulesPress\Foundation\Http\Contracts;

use Attribute;

/**
 * Interface PipeTransform
 *
 * This interface defines a contract for pipe transformations in the framework.
 * A pipe is a mechanism for processing and transforming data during request handling,
 * typically used for validation, sanitization, or transformation before passing it to
 * other parts of the plugin.
 */
#[Attribute(Attribute::TARGET_CLASS)]
interface PipeTransform
{
    /**
     * Transforms the provided value.
     *
     * This method is called to transform the given value, such as sanitizing input data,
     * validating it, or applying other transformations. The result of the transformation
     * is returned and will be used in further processing.
     *
     * @param mixed $value The value to be transformed. It could be any data type.
     * @return mixed The transformed value, which may be of the same or a different type.
     */
    public function transform(mixed $value): mixed;
}
