<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 505 HTTP Version Not Supported error.
 *
 * This exception is thrown when the server does not support the HTTP protocol version 
 * that was used in the request.
 * It is a specific implementation of the HttpException with a predefined HTTP status code of 505 (HTTP Version Not Supported).
 */
class HttpVersionNotSupportedHttpException extends HttpException
{
    /**
     * Constructs a new HttpVersionNotSupportedHttpException.
     *
     * @param string $message The exception message (default is "HTTP Version Not Supported").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'HTTP Version Not Supported',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 505, $reason, $previous);
    }
}
