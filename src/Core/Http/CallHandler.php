<?php

namespace ModulesPress\Core\Http;

use Closure;

/**
 * Handles the invocation of a callable handler in a processing pipeline.
 */
final class CallHandler
{
    /**
     * @var Closure The next handler in the pipeline to be executed.
     */
    private Closure $nextHandler;

    /**
     * Constructor for the CallHandler.
     *
     * @param Closure $nextHandler A closure representing the next handler in the processing chain.
     */
    public function __construct(Closure $nextHandler)
    {
        $this->nextHandler = $nextHandler;
    }

    /**
     * Executes the handler and returns its result.
     *
     * @return mixed The result of the handler execution.
     */
    public function handle(): mixed
    {
        return ($this->nextHandler)();
    }
}
