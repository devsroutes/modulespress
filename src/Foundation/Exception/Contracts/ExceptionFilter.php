<?php

namespace ModulesPress\Foundation\Exception\Contracts;

use ModulesPress\Foundation\Exception\BaseException;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Foundation\Http\Responses\HtmlResponse;
use ModulesPress\Foundation\Http\Responses\JsonResponse;
use WP_REST_Response;

/**
 * Interface ExceptionFilter
 *
 * The `ExceptionFilter` interface is responsible for defining the structure of an exception filter.
 * Implementing classes must define how exceptions are handled and specify the type of response that 
 * should be returned, based on the exception and the execution context.
 *
 * Exception filters are applied when an exception occurs during the execution of a request, 
 * and they allow for custom handling of exceptions (such as logging, transforming, or formatting the 
 * response).
 *
 */
interface ExceptionFilter
{
    /**
     * Handles the provided exception and returns a suitable response.
     *
     * @param BaseException $exception The exception that needs to be caught and handled.
     * @param ExecutionContext $executionContext The context in which the exception occurred, including any relevant request data.
     *
     * @return WP_REST_Response|HtmlResponse|JsonResponse The appropriate response based on the exception and context.
     */
    public function catchException(
        BaseException $exception,
        ExecutionContext $executionContext
    ): WP_REST_Response | HtmlResponse | JsonResponse;
}
