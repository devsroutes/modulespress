<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Enums\RequestMethod;
use ModulesPress\Foundation\Http\Route;

/**
 * Class Get
 *
 * This class represents an attribute used to define HTTP GET routes for the WordPress REST API.
 * It is applied to methods that should handle GET requests for specific API endpoints.
 * The class extends the `Route` class, which defines the routing behavior for REST API requests.
 *
 * The attribute allows you to specify the API endpoint path for the GET route, and it automatically associates
 * the method with a `GET` HTTP request. This helps in binding methods to REST API endpoints within WordPress.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Get extends Route
{
    /**
     * Constructor for the Get route attribute.
     *
     * This constructor initializes the `Get` route attribute with a specified path, 
     * which corresponds to the URL pattern for the API endpoint. The method type is automatically set to `GET`.
     *
     * @param string $path The path for the GET route in the REST API (defaults to `/`).
     */
    public function __construct(private readonly string $path = "/")
    {
        parent::__construct(method: RequestMethod::GET, path: $path);
    }
}
