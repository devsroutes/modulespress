<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 410 Gone HTTP error.
 *
 * This exception is thrown when the resource requested by the client is no longer available 
 * and has been permanently removed from the server, with no forwarding address.
 * It is a specific implementation of the HttpException with a predefined HTTP status code of 410 (Gone).
 */
class GoneHttpException extends HttpException
{
    /**
     * Constructs a new GoneHttpException.
     *
     * @param string $message The exception message (default is "Gone").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Gone',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 410, $reason, $previous);
    }
}
