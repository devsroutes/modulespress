<?php

namespace ModulesPress\Core\Http;

use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;

final class MiddlewareConsumer
{
    /**
     * Stack of registered middlewares, along with their exclusions and routes.
     *
     * @var array<array{middlewares: array, exclusions: array, routes: array}>
     */
    private $middlewaresStack = [];

    /**
     * Adds middlewares to the stack for processing.
     *
     * @param mixed ...$middlewares List of middlewares to apply.
     * @return self Returns the instance for method chaining.
     */
    public function apply(...$middlewares): self
    {
        $this->middlewaresStack[] = [
            'middlewares' => $middlewares,
            'exclusions' => [],
            'routes' => []
        ];

        return $this;
    }

    /**
     * Excludes specific paths or rules from the last registered middleware(s) set.
     *
     * @param mixed ...$exclusions List of exclusions to apply.
     * @return self Returns the instance for method chaining.
     */
    public function exclude(...$exclusions): self
    {
        $currentIndex = count($this->middlewaresStack) - 1;
        foreach ($exclusions as $exclusion) {
            $this->middlewaresStack[$currentIndex]['exclusions'][] = $this->normalizeRule($exclusion);
        }
        return $this;
    }

    /**
     * Retrieves the current stack of middlewares with their configurations.
     *
     * @return array<array{middlewares: array, exclusions: array, routes: array}>
     */
    public function getMiddlewaresStack(): array
    {
        return $this->middlewaresStack;
    }

    /**
     * Specifies the routes for the last registered middleware(s) set.
     *
     * @param mixed ...$routes List of routes to apply the middleware to.
     * @return self Returns the instance for method chaining.
     */
    public function forRoutes(...$routes): self
    {
        $currentIndex = count($this->middlewaresStack) - 1;
        foreach ($routes as $route) {
            $this->middlewaresStack[$currentIndex]['routes'][] = $this->normalizeRule($route);
        }
        return $this;
    }

    /**
     * Normalizes a rule for middleware processing.
     *
     * @param string|array $rule The rule to normalize. Can be a string or an array.
     * @return array Normalized rule with `path` and `methods` keys.
     * @throws ModuleResolutionException If the rule structure is invalid.
     */
    private function normalizeRule($rule): array
    {
        if (is_string($rule)) {
            return [
                'path' => $rule,
                'methods' => ["*"]
            ];
        } elseif (is_array($rule)) {
            if (!is_string($rule["path"])) {
                throw new ModuleResolutionException("Path must be a string.");
            }
            if (!is_array($rule["methods"])) {
                throw new ModuleResolutionException("Methods must be an array.");
            }
            return [
                'path' => $rule["path"],
                'methods' => $rule["methods"]
            ];
        }
        throw new ModuleResolutionException("Invalid rule format give for middleware.");
    }
}
