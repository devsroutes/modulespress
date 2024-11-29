<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Enums\RequestMethod;
use ModulesPress\Foundation\Http\Route;

/**
 * Class Put
 *
 * This class represents an attribute used to define HTTP PUT routes for the WordPress REST API.
 * It is applied to methods that should handle PUT requests for specific API endpoints.
 * The class extends the `Route` class, which handles the routing behavior for REST API requests.
 *
 * The attribute allows you to specify the API endpoint path for the PUT route, and it automatically associates
 * the method with a `PUT` HTTP request. PUT routes are typically used for updating entire resources in a REST API.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Put extends Route
{
    /**
     * Constructor for the Put route attribute.
     *
     * This constructor initializes the `Put` route attribute with a specified path, 
     * which corresponds to the URL pattern for the API endpoint. The method type is automatically set to `PUT`.
     *
     * @param string $path The path for the PUT route in the REST API (defaults to `/`).
     */
    public function __construct(private readonly string $path = "/")
    {
        parent::__construct(method: RequestMethod::PUT, path: $path);
    }
}
