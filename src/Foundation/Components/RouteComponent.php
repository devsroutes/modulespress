<?php

namespace ModulesPress\Foundation\Components;

use ReflectionClass;
use ReflectionMethod;

use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\Http\Route;

/**
 * Class RouteComponent
 *
 * The `RouteComponent` class represents a component associated with a route in the ModulesPress framework.
 * It extends from `ControllerComponent` and adds a specific reference to a `Route` object. This class holds the 
 * necessary reflection information about the controller and its method, as well as the associated route.
 *
 */
class RouteComponent extends ControllerComponent
{
    /**
     * RouteComponent constructor.
     *
     * Initializes the route component with the resolved module, controller, reflection data for the controller class 
     * and method, and the route instance.
     *
     * @param ResolvedModule $resolvedModule The resolved module associated with this route component.
     * @param RestController $controller The controller associated with this route.
     * @param ReflectionClass $controllerClassReflection The reflection of the controller class.
     * @param ReflectionMethod $controllerMethodReflection The reflection of the controller method.
     * @param Route $route The route instance associated with this component.
     */
    public function __construct(
        private readonly ResolvedModule $resolvedModule,
        private readonly RestController $controller,
        private readonly ReflectionClass $controllerClassReflection,
        private readonly ReflectionMethod $controllerMethodReflection,
        private readonly Route $route
    ) {
        parent::__construct($resolvedModule, $controller, $controllerClassReflection, $controllerMethodReflection);
    }

    /**
     * Get the route instance associated with this component.
     *
     * @return Route The route instance.
     */
    public function getRoute(): Route
    {
        return $this->route;
    }
}
