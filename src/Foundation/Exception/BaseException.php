<?php

namespace ModulesPress\Foundation\Exception;

use Exception;
use Throwable;

/**
 * Abstract base class for custom exceptions in the framework.
 * Provides additional functionality to enrich exceptions with contextual data, errors, and reflection-based file/line information.
 */
abstract class BaseException extends Exception
{
    /**
     * @var array $data Additional data associated with the exception.
     */
    protected array $data = [];

    /**
     * @var array $errors Specific errors related to the exception.
     */
    protected array $errors = [];

    /**
     * @param $message The exception message.
     * @param $code The exception code.
     * @param string $reason Additional reason or explanation for the exception.
     * @param Throwable|null $previous The previous exception for chaining.
     */
    public function __construct(
        protected $message = "",
        protected $code = 0,
        protected string $reason = "",
        protected ?Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Sets the exception message.
     *
     * @param string $message The new message.
     * @return $this
     */
    public function setMessage(string $message): static
    {
        $this->message = $message;
        return $this;
    }

    /**
     * Sets the exception code.
     *
     * @param int $code The new code.
     * @return $this
     */
    public function setCode(int $code): static
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Sets the reason for the exception.
     *
     * @param string $reason The reason for the exception.
     * @return $this
     */
    public function setReason(string $reason): static
    {
        $this->reason = $reason;
        return $this;
    }

    /**
     * Sets additional data for the exception.
     *
     * @param array $data The data to associate with the exception.
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Attaches a key-value pair to the exception's data.
     *
     * @param string $key The key for the data.
     * @param mixed $value The value to associate.
     * @return $this
     */
    public function attachData(string $key, mixed $value): static
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * Sets the errors associated with the exception.
     *
     * @param array $errors The errors to associate with the exception.
     * @return $this
     */
    public function setErrors(array $errors): static
    {
        $this->errors = $errors;
        return $this;
    }

    /**
     * Attaches a specific error to the exception's errors.
     *
     * @param string $key The key for the error.
     * @param mixed $value The error value.
     * @return $this
     */
    public function attachError(string $key, mixed $value): static
    {
        $this->errors[$key] = $value;
        return $this;
    }

    /**
     * Sets the line number where the exception occurred.
     *
     * @param int $line The line number.
     * @return $this
     */
    public function setLine(int $line): static
    {
        $this->line = $line;
        return $this;
    }

    /**
     * Sets the file name where the exception occurred.
     *
     * @param string $file The file name.
     * @return $this
     */
    public function setFile(string $file): static
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Retrieves the reason for the exception.
     *
     * @return string The reason for the exception.
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * Retrieves the additional data associated with the exception.
     *
     * @return array The data array.
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Retrieves the errors associated with the exception.
     *
     * @return array The errors array.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Sets file and line information for the exception based on a class reflection.
     *
     * @param string $class The class name.
     * @return $this
     */
    public function forClass(string $class): static
    {
        $refClass = new \ReflectionClass($class);
        $this->setFileAndLine($refClass);
        return $this;
    }

    /**
     * Sets file and line information for the exception based on a class method reflection.
     *
     * @param string $class The class name.
     * @param string $method The method name.
     * @return $this
     */
    public function forClassMethod(string $class, string $method): static
    {
        $refMethod = new \ReflectionMethod($class, $method);
        $this->setFileAndLine($refMethod);
        return $this;
    }

    /**
     * Sets file and line information using a reflection object.
     *
     * @param \ReflectionMethod|\ReflectionClass $ref The reflection object.
     * @return $this
     */
    private function setFileAndLine(\ReflectionMethod|\ReflectionClass $ref): static
    {
        $this->setFile($ref->getFileName());
        $this->setLine($ref->getStartLine());
        return $this;
    }
}
