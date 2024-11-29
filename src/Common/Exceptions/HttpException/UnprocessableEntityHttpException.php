<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 422 Unprocessable Entity HTTP error.
 *
 * This exception is thrown when the server understands the content type of the request entity
 * but was unable to process the contained instructions. Typically used for validation errors.
 */
class UnprocessableEntityHttpException extends HttpException
{
    /**
     * Constructs a new UnprocessableEntityHttpException.
     *
     * @param string $message The exception message (default is "Unprocessable Entity").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Unprocessable Entity',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 422, $reason, $previous);
    }
}
