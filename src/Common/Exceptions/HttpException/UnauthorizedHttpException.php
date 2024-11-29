<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 401 Unauthorized HTTP error.
 *
 * This exception is thrown when the request lacks valid authentication credentials or when the user is not authorized to access
 * the requested resource.
 */
class UnauthorizedHttpException extends HttpException
{
    /**
     * Constructs a new UnauthorizedHttpException.
     *
     * @param string $message The exception message (default is "Unauthorized").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Unauthorized',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 401, $reason, $previous);
    }
}
