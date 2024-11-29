<?php

namespace ModulesPress\Core\ExceptionHandler;

use Exception;
use ReflectionClass;
use Throwable;
use WP_REST_Response;

use ModulesPress\Common\Exceptions\HttpException\InternalServerErrorHttpException;
use ModulesPress\Core\AttributesScanner\AttributesScanner;
use ModulesPress\Core\Core;
use ModulesPress\Foundation\Exception\BaseException;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\Renderer\Renderer;
use ModulesPress\Foundation\Exception\Contracts\ExceptionFilter;
use ModulesPress\Foundation\Http\Responses\HtmlResponse;
use ModulesPress\Foundation\Http\Responses\JsonResponse;

/**
 * The ExceptionHandler class is responsible for managing exceptions within the system. 
 * It handles exceptions by utilizing exception filters, which are used to 
 * customize how different types of exceptions are processed and returned. 
 * It supports both REST API responses and traditional HTML/JSON responses based on the exception.
 */
final class ExceptionHandler
{
    /**
     * @var ResolvedFilter[] Array of exception filters that will be applied to exceptions.
     */
    private $exceptionFilters = [];

    /**
     * ExceptionHandler constructor.
     *
     * @param Core $core The core system, providing plugin and resolver functionality.
     * @param ExecutionContext $executionContext The execution context for the current process.
     * @param Renderer $renderer The renderer used for generating responses.
     */
    public function __construct(
        private readonly Core $core,
        private readonly ExecutionContext $executionContext,
        private readonly Renderer $renderer
    ) {}

    /**
     * Handles the given exception by applying exception filters and generating 
     * an appropriate response based on the exception type.
     *
     * @param Throwable|BaseException $exception The exception to handle.
     */
    public function handleException($exception)
    {
        // If the exception is of type FinalizedRESTResponse, rethrow it
        if ($exception instanceof FinalizedRESTResponse) {
            throw $exception;
        }

        // If the exception is not a BaseException, handle it as an unknown exception
        if (!$exception instanceof BaseException) {
            $this->handleException($this->handleUnknownException($exception));
        }

        try {
            // Retrieve global filters if available
            $globalFilters =  $this->core->getPluginContainer()->getGlobalFilters();
        } catch (\Throwable $th) {
            $exception = $this->handleUnknownException($th);
            $this->processFinalExceptionResponse(
                (new CoreExceptionFilter($this->renderer, $this->core->getPlugin()->isDebugMode()))->catchException($exception->getPrevious(), $this->executionContext)
            );
        }

        // Apply global filters and exception filters in reverse order
        foreach (
            array_reverse(
                array_merge(
                    $globalFilters,
                    $this->getExceptionFilters()
                )
            ) as $resolvedFilter
        ) {
            try {
                // Check if the resolved filter is valid and matches the exception type
                if ($resolvedFilter instanceof ExceptionFilter) {
                    $filterRefClass = new ReflectionClass($resolvedFilter);
                } else {
                    $filterRefClass = new ReflectionClass($resolvedFilter->getFilter());
                }

                $acceptedExceptions = AttributesScanner::scanCatchExceptionsFromFilter($filterRefClass);
                $matchedExceptions = count(array_filter($acceptedExceptions, fn($extClass) => is_a($exception, $extClass)));

                // Apply the filter if it matches the exception
                if (empty($acceptedExceptions) || $matchedExceptions > 0) {
                    $filter = $resolvedFilter instanceof ExceptionFilter
                        ? $resolvedFilter
                        : $this->core->getResolver()->resolve(
                            $resolvedFilter->getResolvedModule(),
                            $resolvedFilter->getFilter(),
                            true
                        );

                    $this->processFinalExceptionResponse(
                        $filter->catchException($exception, $this->executionContext)
                    );
                    break;
                }
            } catch (FinalizedRESTResponse $fe) {
                // Handle FinalizedRESTResponse type exception
                if (!$this->executionContext->switchToRESTContext()) {
                    wp_die("Sending 'FinalizedRESTResponse' is not allowed in Non-REST context. You should double check your Exception Filter '" . $filterRefClass->getName() . "'", "FinalizedRESTResponse", 500);
                }
                throw $fe;
            } catch (\Throwable $exception) {
                if (!$exception instanceof BaseException) {
                    $exception = $this->handleUnknownException($exception);
                    break;
                }
            }
        }

        // If no filter processed the exception, use the core exception filter
        $this->processFinalExceptionResponse(
            (new CoreExceptionFilter($this->renderer, $this->core->getPlugin()->isDebugMode()))->catchException($exception, $this->executionContext)
        );
    }

    /**
     * Processes the final response based on the type of the response object.
     *
     * @param WP_REST_Response|HtmlResponse|JsonResponse $response The response object to send.
     */
    private function processFinalExceptionResponse(
        WP_REST_Response | HtmlResponse | JsonResponse $response
    ) {
        // Handle WP_REST_Response type
        if ($response instanceof WP_REST_Response) {
            throw new FinalizedRESTResponse($response);
        }
        // Handle JsonResponse type
        else if ($response instanceof JsonResponse) {
            foreach ($response->getHeaders() as $header => $value) {
                header("$header: $value");
            }
            wp_send_json($response->getData(), $response->getStatusCode());
        }
        // Handle HtmlResponse type
        else if ($response instanceof HtmlResponse) {
            foreach ($response->getHeaders() as $header => $value) {
                header("$header: $value");
            }
            status_header($response->getStatusCode());
            echo $response->getHtml();
            die();
        }
        // Handle unknown response type
        else {
            wp_die("Unknown response type", "UnknownResponse", 500);
        }
    }

    /**
     * Adds an exception filter to the handler.
     *
     * @param ResolvedFilter $filter The filter to add.
     */
    public function addExceptionFilter(ResolvedFilter $filter)
    {
        $this->exceptionFilters[] = $filter;
    }

    /**
     * Removes an exception filter by its key.
     *
     * @param string $key The key of the filter to remove.
     */
    public function removeExceptionFilters(string $key)
    {
        $this->exceptionFilters = array_values(
            array_filter($this->exceptionFilters, function ($filter) use ($key) {
                return $filter->getKey() !== $key;
            })
        );
    }

    /**
     * Retrieves the list of exception filters.
     *
     * @return ResolvedFilter[] Array of exception filters.
     */
    private function getExceptionFilters()
    {
        return $this->exceptionFilters;
    }

    /**
     * Handles an unknown exception by wrapping it into a BaseException.
     *
     * @param Exception|Throwable $exception The exception to handle.
     *
     * @return BaseException The wrapped BaseException.
     */
    private function handleUnknownException(
        Exception | Throwable $exception
    ): BaseException {
        return (new InternalServerErrorHttpException(previous: $exception))
            ->setReason($this->core->getPlugin()->isDebugMode() ? $exception->getMessage() : "")
            ->setLine($exception->getLine())
            ->setFile($exception->getFile());
    }
}
