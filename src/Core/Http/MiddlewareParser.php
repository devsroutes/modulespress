<?php

namespace ModulesPress\Core\Http;

class MiddlewareParser
{
    /**
     * Determines if a middleware rule is applicable to a given route.
     *
     * @param array $rule Array containing `path` and `methods` keys.
     * @param string $activeRoutePath The current route path being evaluated.
     * @param string $activeRouteMethod The HTTP method of the active route.
     * @return bool Returns true if the middleware rule applies, otherwise false.
     */
    public static function isMiddlewareApplicable(array $rule, string $activeRoutePath, string $activeRouteMethod): bool
    {
        $path = $rule["path"];
        $methods = $rule["methods"];
        return self::pathMatch($path, $activeRoutePath) && self::methodMatch($methods, $activeRouteMethod);
    }

    /**
     * Matches a route path against a given pattern.
     *
     * @param string $pattern The pattern to match (can be a string, wildcard `*`, or a regex).
     * @param string $activeRoutePath The current route path being evaluated.
     * @return bool Returns true if the path matches the pattern, otherwise false.
     */
    private static function pathMatch(string $pattern, string $activeRoutePath): bool
    {
        // Match regex patterns
        if (preg_match('/^#.*#$/', $pattern)) {
            return (bool) preg_match($pattern, $activeRoutePath);
        }

        // Match wildcard
        if ("*" === $pattern) {
            return true;
        }

        // Match exact path
        return $pattern === $activeRoutePath;
    }

    /**
     * Matches an HTTP method against a list of allowed methods.
     *
     * @param array $methods List of HTTP methods or a wildcard `*`.
     * @param string $activeRouteMethod The HTTP method of the active route.
     * @return bool Returns true if the method matches one of the allowed methods, otherwise false.
     */
    private static function methodMatch(array $methods, string $activeRouteMethod): bool
    {
        foreach ($methods as $method) {
            // Match wildcard
            if ("*" === $method) {
                return true;
            }

            // Match specific method
            if ($method->value === $activeRouteMethod) {
                return true;
            }
        }

        return false;
    }
}
