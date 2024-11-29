<?php

namespace ModulesPress\Common\Exceptions\FrameworkException;

use ModulesPress\Foundation\Exception\BaseException;

/**
 * Abstract base class for framework-specific exceptions.
 */
abstract class FrameworkException extends BaseException
{
    /**
     * Constructs a new FrameworkException.
     *
     * @param string $message The exception message.
     * @param int $statusCode The HTTP status code or other relevant status code for the exception.
     * @param string $reason A detailed reason or explanation for the exception.
     * @param \Throwable|null $previous The previous exception for exception chaining.
     */
    public function __construct(
        string $message,
        int $statusCode,
        string $reason,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $reason, $previous);
    }
}
