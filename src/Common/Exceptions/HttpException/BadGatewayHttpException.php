<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 502 Bad Gateway HTTP error.
 *
 * This exception is thrown when a server acting as a gateway or proxy 
 * receives an invalid response from the upstream server. It is a specific
 * implementation of the HttpException with a predefined HTTP status code 
 * of 502 (Bad Gateway).
 */
class BadGatewayHttpException extends HttpException
{
    /**
     * Constructs a new BadGatewayHttpException.
     *
     * @param string $message The exception message (default is "Bad Gateway").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Bad Gateway',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 502, $reason, $previous);
    }
}
