<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 412 Precondition Failed HTTP error.
 *
 * This exception is thrown when one or more preconditions given in the request header fields evaluated to false
 * when tested on the server. The request can be considered valid, but the server cannot perform it due to a failed
 * precondition.
 */
class PreconditionFailedHttpException extends HttpException
{
    /**
     * Constructs a new PreconditionFailedHttpException.
     *
     * @param string $message The exception message (default is "Precondition Failed").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Precondition Failed',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 412, $reason, $previous);
    }
}
