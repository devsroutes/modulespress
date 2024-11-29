<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Contracts\Interceptor;

/**
 * Class UseInterceptors
 *
 * This attribute is used to apply **interceptors** to methods or classes. Interceptors allow you to modify the 
 * request/response flow, enabling cross-cutting concerns like logging, authentication, or transformation of 
 * request and response data.
 *
 * The interceptors specified by this attribute will be applied to the method or class where the attribute is 
 * used. The interceptors can be passed as class names (strings) or instances of classes implementing the 
 * `Interceptor` contract.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::TARGET_CLASS)]
final class UseInterceptors
{
    /**
     * An array of interceptors applied to the method or class.
     *
     * This can be a list of interceptor class names (strings) or instances of interceptor classes
     * that implement the `Interceptor` interface. These interceptors will be invoked during the 
     * request/response lifecycle.
     *
     * @var string[]|Interceptor[] An array of interceptor class names or instances.
     */
    private array $interceptors = [];

    /**
     * Constructor for the UseInterceptors attribute.
     *
     * This constructor allows the definition of one or more interceptors to be applied to the
     * method or class. Interceptors can be passed either as class names (strings) or instances
     * of classes that implement the `Interceptor` interface.
     *
     * @param string|Interceptor ...$interceptors One or more interceptors to apply to the method or class.
     */
    public function __construct(...$interceptors)
    {
        $this->interceptors = $interceptors;
    }

    /**
     * Get the list of interceptors applied to the method or class.
     *
     * This method returns the array of interceptors that were passed to the constructor.
     * It can be a mix of interceptor class names (strings) or instances of classes that implement
     * the `Interceptor` interface.
     *
     * @return string[]|Interceptor[] The list of interceptors.
     */
    public function getInterceptors(): array
    {
        return $this->interceptors;
    }
}
