<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 400 Bad Request HTTP error.
 *
 * This exception is thrown when the server cannot process the request 
 * due to client-side issues, such as malformed syntax, invalid parameters, 
 * or missing required data. It is a specific implementation of the HttpException 
 * with a predefined HTTP status code of 400 (Bad Request).
 */
class BadRequestHttpException extends HttpException
{
    /**
     * Constructs a new BadRequestHttpException.
     *
     * @param string $message The exception message (default is "Bad Request").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Bad Request',
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 400, $reason, $previous);
    }
}
