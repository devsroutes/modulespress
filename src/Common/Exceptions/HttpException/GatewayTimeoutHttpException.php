<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 504 Gateway Timeout HTTP error.
 *
 * This exception is thrown when the server, while acting as a gateway or proxy, 
 * did not receive a timely response from an upstream server it needed to access
 * in order to complete the request. It is a specific implementation of the HttpException
 * with a predefined HTTP status code of 504 (Gateway Timeout).
 */
class GatewayTimeoutHttpException extends HttpException
{
    /**
     * Constructs a new GatewayTimeoutHttpException.
     *
     * @param string $message The exception message (default is "Gateway Timeout").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Gateway Timeout',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 504, $reason, $previous);
    }
}
