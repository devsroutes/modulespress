<?php

namespace ModulesPress\Foundation\Exception\Attributes;

use Attribute;

/**
 * Class CatchException
 *
 * The `CatchException` attribute is used to mark a class that should handle specific exceptions during 
 * execution. The exceptions specified in this attribute will be caught and handled by the class 
 * that is marked with it. This allows for more targeted exception handling based on the exceptions 
 * the class is intended to manage.
 *
 * The exceptions can be passed as arguments to the constructor, and the class will handle them accordingly.
 *
 * @package ModulesPress\Foundation\Exception\Attributes
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class CatchException
{
    /**
     * List of exceptions that the class should accept and handle.
     *
     * @var array
     */
    private array $acceptedExceptions = [];

    /**
     * CatchException constructor.
     *
     * @param string ...$acceptedExceptions A list of exceptions that the class should catch and handle.
     */
    public function __construct(...$acceptedExceptions)
    {
        $this->acceptedExceptions = $acceptedExceptions;
    }

    /**
     * Get the list of accepted exceptions.
     *
     * @return array List of exceptions that the class handles.
     */
    public function getAcceptedExceptions(): array
    {
        return $this->acceptedExceptions;
    }
}
