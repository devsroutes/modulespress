<?php

namespace ModulesPress\Foundation\Http\Attributes;

use Attribute;

/**
 * Class RestController
 *
 * This class represents an attribute used to mark a class as a **REST API controller** in the WordPress REST API.
 * It is applied to controller classes that handle requests and define routes for a specific namespace.
 * The `RestController` attribute binds the class to a specific namespace, which is used to register the routes
 * and associate them with the respective controller methods.
 *
 * The `namespace` defines the base path for the controller's routes, and the class methods are used to handle
 * specific HTTP requests (such as GET, POST, PUT, DELETE) for that namespace.
 */
#[Attribute(Attribute::TARGET_CLASS)]
final class RestController
{
    /**
     * The namespace for the REST API controller.
     *
     * This namespace is used as the base path for the controller's routes.
     * All routes defined within the controller will be prefixed with this namespace.
     *
     * @var string
     */
    public string $namespace;

    /**
     * Constructor for the RestController attribute.
     *
     * This constructor initializes the `RestController` attribute with the specified namespace.
     * The namespace will be used to register routes for the controller and associate them with specific methods.
     *
     * @param string $namespace The namespace that this controller handles in the REST API.
     */
    public function __construct(string $namespace)
    {
        $this->namespace = $namespace;
    }
}
