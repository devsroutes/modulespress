<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 409 Conflict HTTP error.
 *
 * This exception is thrown when the request cannot be completed due to a 
 * conflict with the current state of the resource. It is a specific 
 * implementation of the HttpException with a predefined HTTP status code 
 * of 409 (Conflict).
 */
class ConflictHttpException extends HttpException
{
    /**
     * Constructs a new ConflictHttpException.
     *
     * @param string $message The exception message (default is "Conflict").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Conflict',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 409, $reason, $previous);
    }
}
