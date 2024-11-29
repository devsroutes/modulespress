<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Foundation\Exception\BaseException;

/**
 * Base class for HTTP-related exceptions.
 * 
 * This class is used as the foundation for creating specific HTTP exceptions in the framework,
 * such as 404 (Not Found) or 500 (Internal Server Error). It extends from the BaseException 
 * to provide common exception handling functionality.
 */
abstract class HttpException extends BaseException
{
    /**
     * Constructs a new HttpException.
     *
     * @param string $message The exception message (e.g., "Page Not Found", "Internal Server Error").
     * @param int $statusCode The HTTP status code corresponding to the error (e.g., 404, 500).
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message,
        int $statusCode,
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $statusCode, $reason, $previous);
    }
}
