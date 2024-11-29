<?php

namespace ModulesPress\Foundation\Checker\Attributes;

use Attribute;
use ModulesPress\Foundation\Guard\Contracts\CanActivate;

/**
 * Class UseChecks
 *
 * The `UseChecks` attribute is used to attach a set of checks to an action or filter method.
 * If used on an `add_action`, the method will not execute unless all specified checks pass.
 * If used on an `add_filter`, the first parameter of the callback is returned by default unless
 * the checks return a specific result.
 *
 */
#[Attribute(Attribute::TARGET_METHOD)]
final class UseChecks
{
    /**
     * @var string[]|CanActivate[] List of checks to be executed before the method is called.
     * This can include strings representing check types or instances of classes that implement the `CanActivate` contract.
     */
    private readonly array $checks;

    /**
     * @var int The default argument number to return if no check result is provided.
     * For filters, this will return the first parameter of the callback by default.
     */
    private readonly int $defaultReturnArgNo;

    /**
     * UseChecks constructor.
     *
     * @param string[]|CanActivate[] $checks List of checks to be performed before the method can be executed.
     * @param int $defaultReturnArgNo The argument number to return if no specific check triggers a return (default is 0).
     */
    public function __construct(array $checks, int $defaultReturnArgNo = 0)
    {
        $this->checks = $checks;
        $this->defaultReturnArgNo = $defaultReturnArgNo;
    }

    /**
     * Get the list of checks to be performed.
     *
     * @return string[]|CanActivate[] The checks that will be evaluated before the action or filter is executed.
     */
    public function getChecks(): array
    {
        return $this->checks;
    }

    /**
     * Get the default argument number to return for filters when no check result triggers a return.
     *
     * @return int The default argument number (defaults to 0 for the first argument).
     */
    public function getDefaultReturnArgNo(): int
    {
        return $this->defaultReturnArgNo;
    }
}
