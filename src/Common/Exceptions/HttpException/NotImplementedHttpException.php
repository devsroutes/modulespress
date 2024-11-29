<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 501 Not Implemented HTTP error.
 *
 * This exception is thrown when the server does not support the functionality required to fulfill the request.
 * A 501 error indicates that the server does not recognize the request method or lacks the capability to fulfill it.
 * Typically, this may occur when the requested HTTP method is not implemented on the server.
 */
class NotImplementedHttpException extends HttpException
{
    /**
     * Constructs a new NotImplementedHttpException.
     *
     * @param string $message The exception message (default is "Not Implemented").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Not Implemented',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 501, $reason, $previous);
    }
}
