<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;
use ModulesPress\Foundation\Http\Enums\RequestMethod;
use ModulesPress\Foundation\Http\Route;

/**
 * Class Post
 *
 * This class represents an attribute used to define HTTP POST routes for the WordPress REST API.
 * It is applied to methods that should handle POST requests for specific API endpoints.
 * The class extends the `Route` class, which handles the routing behavior for REST API requests.
 *
 * The attribute allows you to specify the API endpoint path for the POST route, and it automatically associates
 * the method with a `POST` HTTP request. POST routes are typically used for creating or submitting new resources.
 */
#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class Post extends Route
{
    /**
     * Constructor for the Post route attribute.
     *
     * This constructor initializes the `Post` route attribute with a specified path, 
     * which corresponds to the URL pattern for the API endpoint. The method type is automatically set to `POST`.
     *
     * @param string $path The path for the POST route in the REST API (defaults to `/`).
     */
    public function __construct(private readonly string $path = "/")
    {
        parent::__construct(method: RequestMethod::POST, path: $path);
    }
}
