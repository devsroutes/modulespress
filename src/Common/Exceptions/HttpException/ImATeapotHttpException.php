<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 418 I'm a teapot HTTP error.
 *
 * This exception is thrown when the server refuses to perform the action requested 
 * because it is a teapot (following the humorous HTTP 418 status code from RFC 2324).
 * It is a specific implementation of the HttpException with a predefined HTTP status code of 418 (I'm a teapot).
 */
class ImATeapotHttpException extends HttpException
{
    /**
     * Constructs a new ImATeapotHttpException.
     *
     * @param string $message The exception message (default is "I'm a teapot").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = "I'm a teapot",
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 418, $reason, $previous);
    }
}
