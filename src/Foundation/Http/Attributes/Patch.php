<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Enums\RequestMethod;
use ModulesPress\Foundation\Http\Route;

/**
 * Class Patch
 *
 * This class represents an attribute used to define HTTP PATCH routes for the WordPress REST API.
 * It is applied to methods that should handle PATCH requests for specific API endpoints.
 * The class extends the `Route` class, which defines the routing behavior for REST API requests.
 *
 * The attribute allows you to specify the API endpoint path for the PATCH route, and it automatically associates
 * the method with a `PATCH` HTTP request. This is typically used for partial updates to resources in a REST API.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Patch extends Route
{
    /**
     * Constructor for the Patch route attribute.
     *
     * This constructor initializes the `Patch` route attribute with a specified path, 
     * which corresponds to the URL pattern for the API endpoint. The method type is automatically set to `PATCH`.
     *
     * @param string $path The path for the PATCH route in the REST API (defaults to `/`).
     */
    public function __construct(private readonly string $path = "/")
    {
        parent::__construct(method: RequestMethod::PATCH, path: $path);
    }
}
