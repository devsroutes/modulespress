<?php

namespace ModulesPress\Core\Http;

use Attribute;
use Closure;
use WP_REST_Request;
use WP_REST_Response;

use ModulesPress\Core\Core;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\ExecutionContext\RESTContext;
use ModulesPress\Core\ExceptionHandler\ExceptionHandler;

use ModulesPress\Foundation\Http\Attributes\Body;
use ModulesPress\Foundation\Http\Attributes\Param;
use ModulesPress\Foundation\Http\Attributes\Query;
use ModulesPress\Foundation\Http\Attributes\Render;
use ModulesPress\Foundation\Http\Attributes\WP_REST_Req;
use ModulesPress\Foundation\Http\Attributes\WP_REST_Res;
use ModulesPress\Common\Exceptions\HttpException\UnauthorizedHttpException;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Core\ExceptionHandler\FinalizedRESTResponse;
use ModulesPress\Core\ExceptionHandler\ResolvedFilter;
use ModulesPress\Core\Resolver\ResolvedModule;
use ModulesPress\Foundation\Components\RouteComponent;

/**
 * Core Http class responsible for registering and handling plugin REST API.
 */
final class Http
{
    private readonly MiddlewareConsumer $middlewareConsumer;

    /**
     * Constructor for the Http class.
     * 
     * @param Core $core Core instance for plugin container and resolver.
     * @param ExceptionHandler $exceptionHandler Handles exceptions during the request lifecycle.
     * @param ExecutionContext $executionContext Contains information about the current execution context.
     * @param string $restNamespace The REST namespace used for API endpoints.
     */
    public function __construct(
        private readonly Core $core,
        private readonly ExceptionHandler $exceptionHandler,
        private readonly ExecutionContext $executionContext,
        private readonly string $restNamespace
    ) {
        $this->middlewareConsumer = new MiddlewareConsumer();
    }

    /**
     * Registers necessary hooks for the REST API.
     * 
     * @return Http Returns the instance for method chaining.
     */
    public function registerRequiredHooks(): Http
    {
        add_filter('rest_endpoints', array($this, 'removeIndexNamespaces'));
        add_action('rest_api_init', array($this, 'endpointsInitialize'));
        return $this;
    }

    /**
     * Removes index namespaces from the REST API endpoints.
     * 
     * @param array $restEndpoints The current REST endpoints.
     * @return array Modified REST endpoints.
     */
    public function removeIndexNamespaces($restEndpoints)
    {
        $namespaces = array_map(
            fn(RouteComponent $routeComponent) =>  $this->restNamespace . $routeComponent->getController()->namespace,
            $this->core->getPluginContainer()->getRoutes()
        );

        $namespaces = array_unique($namespaces);

        foreach ($restEndpoints as $route => $routeEndpoint) {
            if (in_array($routeEndpoint["namespace"], $namespaces)) {
                foreach ($routeEndpoint as $endpoint) {
                    if (isset($endpoint["callback"])) {
                        if (!$endpoint["callback"] instanceof Closure) {
                            $restEndpoints[$route] = $restEndpoints[$route . "/"];
                            unset($restEndpoints[$route . "/"]);
                        }
                    }
                }
            }
        }
        return $restEndpoints;
    }

    /**
     * Initializes the routes for the REST API.
     */
    public function endpointsInitialize()
    {
        foreach ($this->core->getPluginContainer()->getRoutes() as $routeComponent) {
            $this->registerCoreRESTRoute($routeComponent);
        }
    }

    /**
     * Gets the middleware consumer instance.
     * 
     * @return MiddlewareConsumer The middleware consumer.
     */
    public function getMiddlewareConsumer(): MiddlewareConsumer
    {
        return $this->middlewareConsumer;
    }

    /**
     * Registers a core REST route with WordPress.
     * 
     * @param RouteComponent $routeComponent The route component to register.
     */
    private function registerCoreRESTRoute(RouteComponent $routeComponent)
    {
        $controller = $routeComponent->getController();
        $route = $routeComponent->getRoute();

        $path = $route->getPath();
        if (strpos($path, ':') !== false) {
            $path = preg_replace('/\:([a-zA-Z0-9_]+)/', '(?P<$1>[^\/]+)', $path);
        }

        register_rest_route(
            $this->restNamespace . $controller->namespace,
            $path,
            [
                'methods' => $route->getRequestMethod(),
                'callback' => function (WP_REST_Request $wpRequest) use ($routeComponent) {
                    $restContext = new RESTContext(
                        $routeComponent->getRoute(),
                        $routeComponent->getController(),
                        $wpRequest,
                        new WP_REST_Response(),
                        $routeComponent->getClassReflection(),
                        $routeComponent->getMethodReflection()
                    );
                    return $this->processIncomingRestRequest($routeComponent->getResolvedModule(), $restContext);
                },
            ]
        );
    }

    /**
     * Processes the incoming REST request, applying necessary middleware, guards, filters, etc.
     * 
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param RESTContext $restContext The context for the current REST request.
     * @return WP_REST_Response The response object after processing.
     */
    private function processIncomingRestRequest(ResolvedModule $resolvedModule, RESTContext $restContext)
    {
        try {

            $this->executionContext->setRESTContext($restContext);

            $wpResponse = $restContext->getWPResponse();
            $handlerParams = $restContext->getMethodReflection()->getParameters();

            $middlewareResult = $this->applyMiddlewares($resolvedModule, $restContext);
            if ($middlewareResult instanceof WP_REST_Response) {
                return $middlewareResult;
            }

            $this->applyExceptionFilters(
                exceptionFilters: AttributesScanner::scanUseExceptionFilters(
                    class: $restContext->getClassReflection(),
                ),
                resolvedModule: $resolvedModule,
                restContext: $restContext
            );

            $this->applyGuards(
                array_merge(
                    $this->core->getPluginContainer()->getGlobalGuards(),
                    AttributesScanner::scanUseGuards(class: $restContext->getClassReflection())
                ),
                $resolvedModule,
                $restContext
            );

            $this->applyExceptionFilters(
                exceptionFilters: AttributesScanner::scanUseExceptionFilters(
                    method: $restContext->getMethodReflection()
                ),
                resolvedModule: $resolvedModule,
                restContext: $restContext
            );

            $this->applyGuards(
                AttributesScanner::scanUseGuards(
                    method: $restContext->getMethodReflection()
                ),
                $resolvedModule,
                $restContext
            );

            $params = $this->parseRequestHandlerParameters($resolvedModule, $restContext, $handlerParams);

            $callable = [
                $this->core->getPluginContainer()->get($restContext->getClassReflection()->getName()),
                $restContext->getMethodReflection()->getName()
            ];

            $baseHandler = function () use ($callable, $params) {
                return call_user_func($callable, ...$params);
            };

            $callHandler = new CallHandler($baseHandler);

            $callHandlerWithInterceptors = $this->applyInterceptors(
                array_merge(
                    $this->core->getPluginContainer()->getGlobalInterceptors(),
                    AttributesScanner::scanUseInterceptors(
                        class: $restContext->getClassReflection(),
                        method: $restContext->getMethodReflection()
                    )
                ),
                $resolvedModule,
                $callHandler
            );

            $result = $callHandlerWithInterceptors->handle();

            $render = $restContext->getMethodReflection()->getAttributes(Render::class);
            if (!empty($render)) {
                $render = $render[0]->newInstance();
                $view = $render->getView();
                header('Content-Type: text/html');
                $output = $this->core->getRenderer()->render($view, $result);
                echo $output;
                die();
            }

            $wpResponse->set_data($result);
        } catch (\Throwable $e) {
            try {
                $this->exceptionHandler->handleException($e);
            } catch (FinalizedRESTResponse $fe) {
                return $fe->getResponse();
            }
        }

        return $wpResponse;
    }

    /**
     * Applies middlewares to the request and returns the response if applicable.
     * 
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param RESTContext $restContext The context for the current REST request.
     * @return WP_REST_Response|null The response if the middleware terminates the request early, or null.
     */
    private function applyMiddlewares(ResolvedModule $resolvedModule, RESTContext $restContext): ?WP_REST_Response
    {
        $namespace = $restContext->getController()->namespace;
        $restRoute = $restContext->getRoute()->getPath();
        $activeRouteMethod = $restContext->getRoute()->getRequestMethod();

        foreach ($this->middlewareConsumer->getMiddlewaresStack() as $middleware) {

            $implementations = $middleware["middlewares"];
            $exclusions = $middleware["exclusions"];
            $routes = $middleware["routes"];
            $activeRoutePath = ltrim($namespace, '/')  . $restRoute;

            foreach ($exclusions as $exclusion) {
                if (MiddlewareParser::isMiddlewareApplicable($exclusion, $activeRoutePath, $activeRouteMethod)) {
                    continue 2;
                }
            }

            foreach ($routes as $route) {
                if (MiddlewareParser::isMiddlewareApplicable($route, $activeRoutePath, $activeRouteMethod)) {
                    foreach ($implementations as $implementation) {
                        if (is_callable($implementation)) {
                            $result =  $implementation($restContext->getWPRequest(), $restContext->getWPResponse());
                            if ($result instanceof WP_REST_Response) {
                                return $result;
                            }
                        } else if (method_exists($implementation, "use")) {
                            $instance = $this->core->getResolver()->resolve($resolvedModule, $implementation, true);
                            $result = $instance->use($restContext->getWPRequest(), $restContext->getWPResponse());
                            if ($result instanceof WP_REST_Response) {
                                return $result;
                            }
                        }
                    }
                    continue 2;
                }
            }
        }

        return null;
    }

    /**
     * Applies guards to the request, checking if the user is authorized to proceed.
     * 
     * @param array $guards List of guard classes to apply.
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param RESTContext $restContext The context for the current REST request.
     * @throws UnauthorizedHttpException If the guard denies access.
     */
    private function applyGuards(
        array $guards,
        ResolvedModule $resolvedModule,
        RESTContext $restContext,
    ) {
        foreach ($guards as $guardClass) {
            $guard = $this->core->getResolver()->resolve($resolvedModule, $guardClass, true);
            if (!$guard->canActivate($this->executionContext)) {
                throw (new UnauthorizedHttpException())->forClassMethod(
                    $restContext->getClassReflection()->getName(),
                    $restContext->getMethodReflection()->getName()
                );
            }
        }
    }

    /**
     * Applies exception filters to modify how exceptions are handled.
     * 
     * @param array $exceptionFilters List of exception filter classes to apply.
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param RESTContext $restContext The context for the current REST request.
     */
    private function applyExceptionFilters(
        array $exceptionFilters,
        ResolvedModule $resolvedModule,
        RESTContext $restContext,
    ) {
        if (!empty($exceptionFilters)) {
            foreach ($exceptionFilters as $exceptionFilter) {
                $this->exceptionHandler->addExceptionFilter(
                    new ResolvedFilter(
                        key: $restContext->getClassReflection()->getName() . '::' . $restContext->getMethodReflection()->getName(),
                        filter: $exceptionFilter,
                        resolvedModule: $resolvedModule
                    )
                );
            }
        }
    }

    /**
     * Applies interceptors to the request.
     * 
     * @param array $interceptors List of interceptor classes to apply.
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param CallHandler $callHandler The handler for invoking the controller method.
     * @return CallHandler The modified call handler with applied interceptors.
     */
    private function applyInterceptors(
        array $interceptors,
        ResolvedModule $resolvedModule,
        CallHandler $nextCallHandler,
    ) {
        foreach ($interceptors as $usableInterceptor) {
            $interceptor = $this->core->getResolver()->resolve($resolvedModule, $usableInterceptor, true);
            $currentHandler = $nextCallHandler;
            $nextCallHandler = new CallHandler(
                function () use ($interceptor, $currentHandler) {
                    return $interceptor->intercept($this->executionContext, $currentHandler); // Pass the current CallHandler
                }
            );
        }
        return $nextCallHandler;
    }

    /**
     * Parses the request parameters based on the attributes defined in the controller method.
     * 
     * @param ResolvedModule $resolvedModule The resolved module for the route.
     * @param RESTContext $restContext The context for the current REST request.
     * @param array $handlerParams The list of parameters expected by the handler method.
     * @return array The parsed parameters ready for the handler.
     */
    private function parseRequestHandlerParameters(ResolvedModule $resolvedModule, RESTContext $restContext, array $handlerParams)
    {
        $requestParser = new RequestParameterParser();
        $params = [];
        $generalPipes = array_merge($this->core->getPluginContainer()->getGlobalPipes(), []);

        foreach (
            AttributesScanner::scanUsePipes(
                class: $restContext->getClassReflection(),
                method: $restContext->getMethodReflection(),
            ) as $pipe
        ) {
            $generalPipes[] = $this->core->getResolver()->resolve($resolvedModule, $pipe, true);
        }

        foreach ($handlerParams as $param) {

            foreach ($param->getAttributes() as $attribute) {
                $attributeInstance = $attribute->newInstance();

                if (
                    $attributeInstance instanceof Body ||
                    $attributeInstance instanceof Query ||
                    $attributeInstance instanceof Param
                ) {
                    $pipes = array_merge($generalPipes, []);
                    foreach ($attributeInstance->getPipes() as $pipe) {
                        $pipes[] = $this->core->getResolver()->resolve($resolvedModule, $pipe, true);
                    }
                }

                switch ($attributeInstance::class) {
                    case WP_REST_Req::class:
                        $params[] = $restContext->getWPRequest();
                        break;
                    case WP_REST_Res::class:
                        $params[] = $restContext->getWPResponse();
                        break;
                    case Body::class:
                        $params[] = $requestParser->parseBodyParameter($param, $restContext->getWPRequest()->get_json_params(), $attributeInstance,  $pipes);
                        break;
                    case Query::class:
                        $params[] = $requestParser->parseQueryParameter($param, $restContext->getWPRequest()->get_query_params(), $attributeInstance, $pipes);
                        break;
                    case Param::class:
                        $params[] = $requestParser->parseParamParameter($param, $restContext->getWPRequest()->get_params(), $attributeInstance, $pipes);
                        break;
                }
            }
        }

        return $params;
    }
}
