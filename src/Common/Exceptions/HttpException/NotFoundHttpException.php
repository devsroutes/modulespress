<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 404 Not Found HTTP error.
 *
 * This exception is thrown when the server cannot find the requested resource. 
 * It indicates that the resource the client tried to access does not exist or is not available.
 * A 404 error is typically displayed when the URL provided by the client does not match any
 * of the server's resources.
 */
class NotFoundHttpException extends HttpException
{
    /**
     * Constructs a new NotFoundHttpException.
     *
     * @param string $message The exception message (default is "Not Found").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Not Found',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 404, $reason, $previous);
    }
}
