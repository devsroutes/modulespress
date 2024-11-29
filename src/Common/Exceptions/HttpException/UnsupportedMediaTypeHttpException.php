<?php

namespace ModulesPress\Common\Exceptions\HttpException;

use ModulesPress\Common\Exceptions\HttpException\HttpException;

/**
 * Exception representing a 415 Unsupported Media Type HTTP error.
 *
 * This exception is thrown when the server refuses to process a request because the media type
 * of the request is unsupported. The server can reject the request if the media type is not
 * acceptable according to the server's capabilities.
 */
class UnsupportedMediaTypeHttpException extends HttpException
{
    /**
     * Constructs a new UnsupportedMediaTypeHttpException.
     *
     * @param string $message The exception message (default is "Unsupported Media Type").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = 'Unsupported Media Type',
        string $reason = '',
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 415, $reason, $previous);
    }
}
