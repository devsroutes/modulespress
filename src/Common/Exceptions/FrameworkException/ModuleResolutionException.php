<?php

namespace ModulesPress\Common\Exceptions\FrameworkException;

use ModulesPress\Common\Exceptions\FrameworkException\FrameworkException;

/**
 * Exception thrown when a module resolution fails within the framework.
 * Extends the FrameworkException to provide specific error handling for module-related issues.
 */
class ModuleResolutionException extends FrameworkException
{
    /**
     * Constructs a new ModuleResolutionException.
     *
     * @param string $message The exception message (default is "Module Resolution Exception").
     * @param string $reason The detailed reason or explanation for the exception (optional).
     * @param \Throwable|null $previous The previous exception for exception chaining (optional).
     */
    public function __construct(
        string $message = "Module Resolution Exception",
        string $reason = "",
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 500, $reason, $previous);
    }
}
