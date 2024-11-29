<?php

namespace ModulesPress\Core\AttributesScanner;

use ReflectionClass;
use ReflectionMethod;
use ModulesPress\Foundation\Exception\Attributes\CatchException;
use ModulesPress\Foundation\Exception\Attributes\UseExceptionFilter;
use ModulesPress\Foundation\Checker\Attributes\UseChecks;
use ModulesPress\Foundation\Guard\Attributes\UseGuards;
use ModulesPress\Foundation\Hookable\Attributes\Add_Action;
use ModulesPress\Foundation\Hookable\Attributes\Add_Filter;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Foundation\Http\Attributes\RestController;
use ModulesPress\Foundation\Http\Attributes\UseInterceptors;
use ModulesPress\Foundation\Http\Attributes\UsePipes;
use ModulesPress\Foundation\Http\Route;
use ModulesPress\Foundation\View\Attributes\ViewCompose;
use ModulesPress\Foundation\View\Attributes\ViewDirective;

/**
 * A utility class for scanning and resolving PHP attributes used in the ModulesPress framework.
 */
abstract class AttributesScanner
{
    /**
     * Checks if a class has a specific attribute.
     *
     * @param string $attribute_class The fully qualified class name of the attribute to check for.
     * @param string $class_name The fully qualified class name to inspect.
     * @return bool True if the class has the attribute, false otherwise.
     */
    public static function haveAttribute(string $attribute_class, string $class_name): bool
    {
        $attributes = (new ReflectionClass($class_name))->getAttributes($attribute_class);
        return !empty($attributes);
    }

    /**
     * Scans for guards on a class or method.
     *
     * @param ReflectionMethod|null $method Optional method to scan for guards.
     * @param ReflectionClass|null $class Optional class to scan for guards.
     * @return array The list of guards found.
     * @throws ModuleResolutionException If both $method and $class are null.
     */
    public static function scanUseGuards(?ReflectionMethod $method = null, ?ReflectionClass $class = null): array
    {
        $guards = [];

        if (!$method && !$class) {
            throw new ModuleResolutionException(reason: 'Cannot resolve guards without a method or class');
        }

        if ($class) {
            $attrs = $class->getAttributes(UseGuards::class);
            foreach ($attrs as $attr) {
                $guards[] = $attr->newInstance()->getGuards();
            }
        }

        if ($method) {
            $attrs = $method->getAttributes(UseGuards::class);
            foreach ($attrs as $attr) {
                $guards[] = $attr->newInstance()->getGuards();
            }
        }

        return array_merge([], ...$guards);
    }

    /**
     * Scans for interceptors on a class or method.
     *
     * @param ReflectionMethod|null $method Optional method to scan for interceptors.
     * @param ReflectionClass|null $class Optional class to scan for interceptors.
     * @return array The list of interceptors found.
     * @throws ModuleResolutionException If both $method and $class are null.
     */
    public static function scanUseInterceptors(?ReflectionMethod $method = null, ?ReflectionClass $class = null): array
    {
        $interceptors = [];

        if (!$method && !$class) {
            throw new ModuleResolutionException(reason: 'Cannot resolve interceptors without a method or class');
        }

        if ($class) {
            $attrs = $class->getAttributes(UseInterceptors::class);
            foreach ($attrs as $attr) {
                $interceptors[] = $attr->newInstance()->getInterceptors();
            }
        }

        if ($method) {
            $attrs = $method->getAttributes(UseInterceptors::class);
            foreach ($attrs as $attr) {
                $interceptors[] = $attr->newInstance()->getInterceptors();
            }
        }

        return array_merge([], ...$interceptors);
    }

    /**
     * Scans for pipes on a class or method.
     *
     * @param ReflectionMethod|null $method Optional method to scan for pipes.
     * @param ReflectionClass|null $class Optional class to scan for pipes.
     * @return array The list of pipes found.
     * @throws ModuleResolutionException If both $method and $class are null.
     */
    public static function scanUsePipes(?ReflectionMethod $method = null, ?ReflectionClass $class = null): array
    {
        $pipes = [];

        if (!$method && !$class) {
            throw new ModuleResolutionException(reason: 'Cannot resolve pipes without a method or class');
        }

        if ($class) {
            $attrs = $class->getAttributes(UsePipes::class);
            foreach ($attrs as $attr) {
                $pipes[] = $attr->newInstance()->getPipes();
            }
        }

        if ($method) {
            $attrs = $method->getAttributes(UsePipes::class);
            foreach ($attrs as $attr) {
                $pipes[] = $attr->newInstance()->getPipes();
            }
        }

        return array_merge([], ...$pipes);
    }

    /**
     * Scans for checks on a method.
     *
     * @param ReflectionMethod $method The method to scan for checks.
     * @return UseChecks[] A list of UseChecks instances.
     */
    public static function scanUseChecks(ReflectionMethod $method): array
    {
        $checks = [];
        $attrs = $method->getAttributes(UseChecks::class);
        foreach ($attrs as $attr) {
            $checks[] = $attr->newInstance();
        }
        return $checks;
    }

    /**
     * Scans for view composers on a method.
     *
     * @param ReflectionMethod $method The method to scan for view composers.
     * @return ViewCompose[] A list of ViewCompose instances.
     */
    public static function scanViewComposers(ReflectionMethod $method): array
    {
        $viewComposers = [];
        $attrs = $method->getAttributes(ViewCompose::class);
        foreach ($attrs as $attr) {
            $viewComposers[] = $attr->newInstance();
        }
        return $viewComposers;
    }

    /**
     * Scans for view directives on a method.
     *
     * @param ReflectionMethod $method The method to scan for view directives.
     * @return ViewDirective[] A list of ViewDirective instances.
     */
    public static function scanViewDirectives(ReflectionMethod $method): array
    {
        $viewDirectives = [];
        $attrs = $method->getAttributes(ViewDirective::class);
        foreach ($attrs as $attr) {
            $viewDirectives[] = $attr->newInstance();
        }
        return $viewDirectives;
    }

    /**
     * Scans for exception filters on a class or method.
     *
     * @param ReflectionMethod|null $method Optional method to scan for filters.
     * @param ReflectionClass|null $class Optional class to scan for filters.
     * @return array A list of exception filters.
     * @throws ModuleResolutionException If both $method and $class are null.
     */
    public static function scanUseExceptionFilters(?ReflectionMethod $method = null, ?ReflectionClass $class = null): array
    {
        $exceptionFilters = [];

        if (!$method && !$class) {
            throw new ModuleResolutionException(reason: 'Cannot resolve exception filters without a method or class');
        }

        if ($class) {
            $attrs = $class->getAttributes(UseExceptionFilter::class);
            foreach ($attrs as $attr) {
                $exceptionFilters[] = $attr->newInstance()->getFilters();
            }
        }

        if ($method) {
            $attrs = $method->getAttributes(UseExceptionFilter::class);
            foreach ($attrs as $attr) {
                $exceptionFilters[] = $attr->newInstance()->getFilters();
            }
        }

        return array_merge([], ...$exceptionFilters);
    }

     /**
     * Scans a filter class for exceptions it can handle.
     *
     * @param ReflectionClass $filterRefClass The reflection class of the exception filter.
     * @return array The list of accepted exceptions that the filter can handle.
     */
    public static function scanCatchExceptionsFromFilter(ReflectionClass $filterRefClass): array
    {
        return $filterRefClass->getAttributes(CatchException::class)[0]->newInstance()->getAcceptedExceptions();
    }

    /**
     * Scans a method for actions defined by Add_Action attributes.
     *
     * @param ReflectionMethod $refMethod The reflection method to scan for actions.
     * @return Add_Action[] A list of Add_Action instances representing the actions added by the method.
     */
    public static function scanAddActions(ReflectionMethod $refMethod): array
    {
        $add_actions = [];
        $attrs = $refMethod->getAttributes(Add_Action::class);
        foreach ($attrs as $attr) {
            $add_actions[] = $attr->newInstance();
        }
        return $add_actions;
    }

    /**
     * Scans a method for filters defined by Add_Filter attributes.
     *
     * @param ReflectionMethod $refMethod The reflection method to scan for filters.
     * @return Add_Filter[] A list of Add_Filter instances representing the filters added by the method.
     */
    public static function scanAddFilters(ReflectionMethod $refMethod): array
    {
        $add_filters = [];
        $attrs = $refMethod->getAttributes(Add_Filter::class);
        foreach ($attrs as $attr) {
            $add_filters[] = $attr->newInstance();
        }
        return $add_filters;
    }

    /**
     * Scans a class for controllers defined by RestController attributes.
     *
     * @param ReflectionClass $refClass The reflection class to scan for controllers.
     * @return RestController[] A list of RestController instances found in the class.
     */
    public static function scanRestControllers(ReflectionClass $refClass): array
    {
        $controllers = [];
        $controller_attributes = $refClass->getAttributes(RestController::class);
        foreach ($controller_attributes as $attr) {
            $controllers[] = $attr->newInstance();
        }
        return $controllers;
    }

    /**
     * Scans a method for routes defined by Route attributes.
     *
     * @param ReflectionMethod $refMethod The reflection method to scan for routes.
     * @return Route[] A list of Route instances representing the routes defined by the method.
     */
    public static function scanRoutes(ReflectionMethod $refMethod): array
    {
        $routes = [];
        $routesAttrs = $refMethod->getAttributes();
        foreach ($routesAttrs as $attr) {
            if (is_subclass_of($attr->getName(), Route::class)) {
                $routes[] = $attr->newInstance();
            }
        }
        return $routes;
    }
}
