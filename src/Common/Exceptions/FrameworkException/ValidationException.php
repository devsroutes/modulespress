<?php

namespace ModulesPress\Common\Exceptions\FrameworkException;

use ModulesPress\Common\Exceptions\FrameworkException\FrameworkException;

/**
 * Exception thrown when a validation error occurs within the framework.
 * Extends the FrameworkException to provide specific error handling for validation-related issues.
 */
class ValidationException extends FrameworkException
{
    /**
     * Constructs a new ValidationException.
     *
     * @param string $message The exception message (default is "Validation Exception").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = "Validation Exception",
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 422, $reason, $previous);
    }
}
