<?php

namespace ModulesPress\Core\ExceptionHandler;

use Exception;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Common\Exceptions\HttpException\InternalServerErrorHttpException;
use WP_REST_Response;

use ModulesPress\Foundation\Exception\Attributes\CatchException;
use ModulesPress\Foundation\Exception\BaseException;
use ModulesPress\Foundation\Exception\Contracts\ExceptionFilter;
use ModulesPress\Core\ExecutionContext\ExecutionContext;
use ModulesPress\Core\Renderer\Renderer;
use ModulesPress\Foundation\Http\Responses\HtmlResponse;
use ModulesPress\Foundation\Http\Responses\JsonResponse;

/**
 * CoreExceptionFilter is an implementation of the ExceptionFilter interface, 
 * designed to catch and handle specific exceptions within the system. 
 * It determines the appropriate response format (REST, JSON, or HTML) 
 * based on the execution context and whether the plugin is in debug mode.
 *
 * This filter is primarily responsible for formatting the exception into 
 * structured responses that can be returned to the user.
 * 
 */
#[CatchException]
class CoreExceptionFilter implements ExceptionFilter
{
    /**
     * @var string $caughtBy The name of the filter or the class that caught the exception.
     */
    protected string $caughtBy;

    /**
     * @var array $displayAdditionalDebuggingFor List of exception classes for which additional debugging information should be displayed.
     */
    protected array $displayAdditionalDebuggingFor = [
        InternalServerErrorHttpException::class,
        ModuleResolutionException::class
    ];

    /**
     * CoreExceptionFilter constructor.
     *
     * @param Renderer $renderer The renderer used for rendering exception HTML content.
     * @param bool $debugMode Whether the plugin is in debug mode.
     * @param string $filter The name of the filter (optional).
     */
    public function __construct(
        private readonly Renderer $renderer,
        private readonly bool $debugMode,
        private readonly string $filter = ""
    ) {
        if ($this->filter) {
            $this->caughtBy = $this->filter;
        } else {
            $this->caughtBy = (new \ReflectionClass(static::class))->getShortName();
        }
    }

    /**
     * Main method that catches the exception and determines the response format (REST, JSON, or HTML).
     *
     * @param BaseException $exception The exception to handle.
     * @param ExecutionContext $executionContext The current execution context.
     * 
     * @return WP_REST_Response|HtmlResponse|JsonResponse The appropriate response format.
     */
    public function catchException(
        BaseException $exception,
        ExecutionContext $executionContext
    ): WP_REST_Response | HtmlResponse | JsonResponse {
        if ($executionContext->switchToRESTContext()) {
            return $this->forRestResponse($exception, $executionContext);
        } else if (wp_is_json_request()) {
            return $this->forJsonResponse($exception, $executionContext);
        } else {
            return $this->forHtmlResponse($exception, $executionContext);
        }
    }

    /**
     * Prepares and returns a REST response for the exception.
     *
     * @param BaseException $exception The exception to handle.
     * @param ExecutionContext $executionContext The current execution context.
     * 
     * @return WP_REST_Response The REST API response.
     */
    protected function forRestResponse(
        BaseException $exception,
        ExecutionContext $executionContext
    ): WP_REST_Response {
        $data = [
            'message' => $exception->getMessage(),
            'statusCode' => $exception->getCode(),
        ];

        $errors = $exception->getErrors();
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }

        if ($this->debugMode) {
            $data['reason'] = $exception->getReason();
            $data = array_merge($data, $this->getDebuggingParams($exception));
        }

        $response = $executionContext->switchToRESTContext()->getWPResponse();
        $response->set_data($data);
        $response->set_status($exception->getCode());

        return $response;
    }

    /**
     * Prepares and returns a JSON response for the exception.
     *
     * @param BaseException $exception The exception to handle.
     * @param ExecutionContext $executionContext The current execution context.
     * 
     * @return JsonResponse The JSON response.
     */
    protected function forJsonResponse(
        BaseException $exception,
        ExecutionContext $executionContext
    ): JsonResponse {
        $data = [
            'message' => $exception->getMessage(),
            'statusCode' => $exception->getCode()
        ];

        $errors = $exception->getErrors();
        if (!empty($errors)) {
            $data['errors'] = $errors;
        }

        if ($this->debugMode) {
            $data['reason'] = $exception->getReason();
            $data = array_merge($data, $this->getDebuggingParams($exception));
        }

        return new JsonResponse($data, $exception->getCode());
    }

    /**
     * Prepares and returns an HTML response for the exception.
     *
     * @param BaseException $exception The exception to handle.
     * @param ExecutionContext $executionContext The current execution context.
     * 
     * @return HtmlResponse The HTML response.
     */
    protected function forHtmlResponse(
        BaseException $exception,
        ExecutionContext $executionContext
    ): HtmlResponse {
        $htmlContent = "";

        if ($this->debugMode) {
            if ($exception->getReason()) {
                $exception->setMessage(
                    $exception->getMessage() . " - Reason: " . $exception->getReason()
                );
            }
            $htmlContent = $this->renderer->renderException($exception, stringFormat: true);
        } else {
            $htmlContent = $this->renderer->renderAsString("errors.generic", ['exception' => $exception]);
        }
        return new HtmlResponse($htmlContent, $exception->getCode());
    }

    /**
     * Retrieves debugging parameters if the exception is one of the specified types.
     *
     * @param BaseException $exception The exception to get debugging parameters for.
     * 
     * @return array The debugging parameters.
     */
    protected function getDebuggingParams(BaseException $exception): array
    {
        if (in_array(get_class($exception), $this->displayAdditionalDebuggingFor)) {
            return [
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'filter' => $this->caughtBy,
                'trace' => $this->getTrace($exception)
            ];
        }
        return [];
    }

    /**
     * Maps the exception trace to a simplified format.
     *
     * @param Exception $exception The exception to extract trace data from.
     * 
     * @return array The simplified trace.
     */
    protected function getTrace(Exception $exception)
    {
        return array_map([$this, 'mapTrace'], $exception->getTrace());
    }

    /**
     * Maps individual trace elements by removing the 'args' key.
     *
     * @param array $trace The trace element to map.
     * 
     * @return array The mapped trace element.
     */
    private function mapTrace(array $trace): array
    {
        unset($trace['args']);
        return $trace;
    }
}
