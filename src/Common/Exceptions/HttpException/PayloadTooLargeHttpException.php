<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 413 Payload Too Large HTTP error.
 *
 * This exception is thrown when the request payload (body) is larger than the server is willing or able to process.
 * It usually occurs when a client tries to send a request with data that exceeds the server's configured size limits.
 */
class PayloadTooLargeHttpException extends HttpException
{
    /**
     * Constructs a new PayloadTooLargeHttpException.
     *
     * @param string $message The exception message (default is "Payload Too Large").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Payload Too Large',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 413, $reason, $previous);
    }
}
