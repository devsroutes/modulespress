<?php

namespace ModulesPress\Foundation\Http;

use ModulesPress\Foundation\Http\Enums\RequestMethod;

/**
 * Class Route
 *
 * Represents an HTTP route definition for RESTful endpoints.
 * This abstract class provides the foundation for defining a route's HTTP method and path.
 *
 */
abstract class Route
{
    /**
     * @param RequestMethod $method The HTTP method associated with the route (e.g., GET, POST).
     * @param string $path The URI path for the route (e.g., `/api/resource`).
     */
    public function __construct(
        private readonly RequestMethod $method,
        private readonly string $path,
    ) {}

    /**
     * Retrieve the HTTP method of the route.
     *
     * @return string The HTTP method as a string (e.g., 'GET', 'POST').
     */
    public function getRequestMethod(): string
    {
        return $this->method->value;
    }

    /**
     * Retrieve the path of the route.
     *
     * @return string The URI path of the route (e.g., `/api/resource`).
     */
    public function getPath(): string
    {
        return $this->path;
    }
}
