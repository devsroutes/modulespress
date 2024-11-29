<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 503 Service Unavailable HTTP error.
 *
 * This exception is thrown when the server is temporarily unable to handle the request due to being overloaded or down for maintenance.
 * It indicates that the server is currently unavailable but may be available again after some time.
 */
class ServiceUnavailableHttpException extends HttpException
{
    /**
     * Constructs a new ServiceUnavailableHttpException.
     *
     * @param string $message The exception message (default is "Service Unavailable").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Service Unavailable',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 503, $reason, $previous);
    }
}
