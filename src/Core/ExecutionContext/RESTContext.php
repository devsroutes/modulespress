<?php

namespace ModulesPress\Core\ExecutionContext;

use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\Http\Route;
use WP_REST_Request;
use WP_REST_Response;

/**
 * The RESTContext class holds information about a REST API request context,
 * including details about the route, controller, request, response, and 
 * the reflections of the controller and method handling the request.
 */
final class RESTContext
{
    public function __construct(
        private readonly Route $route,
        private readonly RestController $controller,
        private readonly WP_REST_Request $request,
        private readonly WP_REST_Response $response,
        private readonly \ReflectionClass $classReflection,
        private readonly \ReflectionMethod $methodReflection,
    ) {}

    /**
     * Gets the REST controller handling the request.
     *
     * @return RestController The controller for the request.
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Gets the route associated with the REST request.
     *
     * @return Route The route for the request.
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets the WordPress REST request object.
     *
     * @return WP_REST_Request The WordPress REST request object.
     */
    public function getWPRequest()
    {
        return $this->request;
    }

    /**
     * Gets the WordPress REST response object.
     *
     * @return WP_REST_Response The WordPress REST response object.
     */
    public function getWPResponse()
    {
        return $this->response;
    }

    /**
     * Gets the reflection of the controller handling the request.
     *
     * @return \ReflectionClass The controller class reflection.
     */
    public function getClassReflection(): \ReflectionClass
    {
        return $this->classReflection;
    }

    /**
     * Gets the reflection of the method handling the REST request.
     *
     * @return \ReflectionMethod The method reflection.
     */
    public function getMethodReflection(): \ReflectionMethod
    {
        return $this->methodReflection;
    }
}
