<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 405 Method Not Allowed HTTP error.
 *
 * This exception is thrown when the HTTP method used in the request is not
 * allowed for the resource. For example, sending a POST request to a URL
 * that only accepts GET requests.
 */
class MethodNotAllowedHttpException extends HttpException
{
    /**
     * Constructs a new MethodNotAllowedHttpException.
     *
     * @param string $message The exception message (default is "Method Not Allowed").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Method Not Allowed',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 405, $reason, $previous);
    }
}
