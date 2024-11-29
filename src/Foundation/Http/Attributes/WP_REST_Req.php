<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;

/**
 * Class WP_REST_Req
 *
 * This attribute is used to indicate that a method parameter should be automatically injected with 
 * the current instance of the WordPress REST API request (`WP_REST_Request`). 
 * The attribute can be applied to controller methods that handle WordPress REST API requests to 
 * access the request data directly.
 *
 * This attribute is typically used for automatic dependency injection of the `WP_REST_Request` object 
 * into a controller method's parameters, eliminating the need to manually fetch the request object.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class WP_REST_Req {}
