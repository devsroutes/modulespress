<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;

/**
 * Class WP_REST_Res
 *
 * This attribute is used to indicate that a method parameter should be automatically injected with 
 * the current instance of the WordPress REST API response (`WP_REST_Response`). 
 * It is typically applied to controller methods that handle WordPress REST API requests, allowing
 * the method to directly modify or interact with the response object.
 *
 * This attribute helps with the automatic injection of the `WP_REST_Response` object into a controller method's
 * parameters, simplifying the code and allowing easier manipulation of the REST API response.
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class WP_REST_Res {}
