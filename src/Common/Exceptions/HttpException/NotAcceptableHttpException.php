<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 406 Not Acceptable HTTP error.
 *
 * This exception is thrown when the server cannot produce a response that
 * is acceptable to the client according to the Accept headers sent in the request.
 * For example, if the server cannot provide the requested resource in a format 
 * that the client is willing to accept, this exception may be thrown.
 */
class NotAcceptableHttpException extends HttpException
{
    /**
     * Constructs a new NotAcceptableHttpException.
     *
     * @param string $message The exception message (default is "Not Acceptable").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Not Acceptable',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 406, $reason, $previous);
    }
}
