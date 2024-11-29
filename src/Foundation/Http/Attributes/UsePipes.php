<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Contracts\PipeTransform;

/**
 * Class UsePipes
 *
 * This attribute is used to apply **pipes** to methods or classes. Pipes allow you to transform request data
 * or modify the execution flow before or after the main handler processes the request.
 *
 * The pipes specified by this attribute will be applied to the method or class where the attribute is used.
 * Pipes can be specified as class names (strings) or instances of classes that implement the `PipeTransform` interface.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class UsePipes
{
    /**
     * An array of pipes applied to the method or class.
     *
     * This can be a list of pipe class names (strings) or instances of pipe classes that implement the 
     * `PipeTransform` interface. These pipes will be applied to the request data or execution flow.
     *
     * @var string[]|PipeTransform[] An array of pipe class names or instances.
     */
    private array $pipes = [];

    /**
     * Constructor for the UsePipes attribute.
     *
     * This constructor allows the definition of one or more pipes to be applied to the method or class.
     * Pipes can be passed either as class names (strings) or instances of classes that implement the 
     * `PipeTransform` interface.
     *
     * @param string|PipeTransform ...$pipes One or more pipes to apply to the method or class.
     */
    public function __construct(...$pipes)
    {
        $this->pipes = $pipes;
    }

    /**
     * Get the list of pipes applied to the method or class.
     *
     * This method returns the array of pipes that were passed to the constructor.
     * It can be a mix of pipe class names (strings) or instances of classes that implement
     * the `PipeTransform` interface.
     *
     * @return string[]|PipeTransform[] The list of pipes.
     */
    public function getPipes(): array
    {
        return $this->pipes;
    }
}
