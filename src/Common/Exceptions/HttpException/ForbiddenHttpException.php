<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 403 Forbidden HTTP error.
 *
 * This exception is thrown when the server understands the request, but 
 * refuses to authorize it. It is a specific implementation of the HttpException
 * with a predefined HTTP status code of 403 (Forbidden).
 */
class ForbiddenHttpException extends HttpException
{
    /**
     * Constructs a new ForbiddenHttpException.
     *
     * @param string $message The exception message (default is "Forbidden").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Forbidden',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 403, $reason, $previous);
    }
}
