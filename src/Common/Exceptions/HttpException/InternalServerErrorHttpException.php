<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 500 Internal Server Error HTTP error.
 *
 * This exception is thrown when the server encounters an unexpected condition
 * that prevents it from fulfilling the request. It is a generic error message
 * indicating that the server encountered an issue it was not prepared to handle.
 */
class InternalServerErrorHttpException extends HttpException
{
    /**
     * Constructs a new InternalServerErrorHttpException.
     *
     * @param string $message The exception message (default is "Internal Server Error").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = "Internal Server Error",
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 500, $reason, $previous);
    }
}
