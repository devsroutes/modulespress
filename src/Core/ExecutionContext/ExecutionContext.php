<?php

namespace ModulesPress\Core\ExecutionContext;

/**
 * The ExecutionContext class manages the current context of the execution, 
 * which could either be related to a REST API request or a hook execution.
 * It allows switching between different contexts (REST and hook) 
 * and maintains a stack of hook contexts for hook-based operations.
 */
final class ExecutionContext
{
    private ?RESTContext $restContext = null;

    /**
     * @var HookContext[] Array of HookContexts that tracks the current hook execution contexts.
     */
    private array $hookContexts = [];

    public function __construct() {}

    /**
     * Sets the current REST context.
     * 
     * @param RESTContext $context The REST context to set.
     */
    public function setRESTContext(RESTContext $context): void
    {
        $this->restContext = $context;
    }

    /**
     * Switches to the current REST context if available.
     *
     * @return RESTContext|null The current REST context or null if not set.
     */
    public function switchToRESTContext(): ?RESTContext
    {
        return $this->restContext;
    }

    /**
     * Pushes a new hook context onto the stack.
     *
     * @param HookContext $context The hook context to push.
     */
    public function pushHookContext(HookContext $context): void
    {
        $this->hookContexts[] = $context;
    }

    /**
     * Pops the last hook context from the stack.
     */
    public function popHookContext(): void
    {
        array_pop($this->hookContexts);
    }

    /**
     * Switches to the current hook context if available.
     *
     * @return HookContext|null The current hook context or null if no context is available.
     */
    public function switchToHookContext(): ?HookContext
    {
        return !empty($this->hookContexts) ? $this->hookContexts[count($this->hookContexts) - 1] : null;
    }
}
