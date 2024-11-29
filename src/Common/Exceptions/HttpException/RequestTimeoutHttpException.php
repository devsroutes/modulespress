<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 408 Request Timeout HTTP error.
 *
 * This exception is thrown when the server times out waiting for the client to send a request.
 * It indicates that the server did not receive a complete request message within the time that it was prepared to wait.
 */
class RequestTimeoutHttpException extends HttpException
{
    /**
     * Constructs a new RequestTimeoutHttpException.
     *
     * @param string $message The exception message (default is "Request Timeout").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Request Timeout',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 408, $reason, $previous);
    }
}
