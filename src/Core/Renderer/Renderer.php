<?php

namespace ModulesPress\Core\Renderer;

use Throwable;
use ModulesPress\Common\Exceptions\FrameworkException\ModuleResolutionException;
use ModulesPress\Core\RenderingEngine\RenderingEngine;

/**
 * Class Renderer
 * 
 * Handles rendering views and exceptions using the RenderingEngine.
 * Provides functionality to render views as strings, output them, or format exceptions.
 */
final class Renderer
{
    /**
     * Renderer constructor.
     * 
     * @param RenderingEngine $renderingEngine The rendering engine to handle templating and exception rendering.
     */
    public function __construct(
        private readonly RenderingEngine $renderingEngine
    ) {}

    /**
     * Renders a view and returns it as a string.
     * 
     * @param string $view The name of the view to render.
     * @param array $data An associative array of data to pass to the view.
     * @return string The rendered view as a string.
     */
    public function renderAsString(string $view, array $data = []): string
    {
        return $this->renderingEngine->getTemplatingEngine()->run($view, $data);
    }

    /**
     * Renders a view and outputs it directly.
     * 
     * @param string $view The name of the view to render.
     * @param array $data An associative array of data to pass to the view.
     * @return void
     */
    public function render(string $view, array $data = []): void
    {
        echo $this->renderAsString($view, $data);
    }

    /**
     * Renders an exception using the exception renderer or a fallback debug formatter.
     * 
     * If the exception renderer is available, it is used to format the exception with enhanced styling.
     * Optionally returns the rendered output as a string.
     * 
     * @param Throwable $exception The exception to render.
     * @param bool $stringFormat If true, returns the rendered exception as a string; otherwise, outputs it.
     * @return null|string Null if output is directly rendered; otherwise, the formatted exception as a string.
     */
    public function renderException(Throwable $exception, bool $stringFormat = false): null | string
    {
        $exceptionRenderer = $this->renderingEngine->getExceptionRenderer();

        if ($exceptionRenderer) {
            // Custom styling for ModuleResolutionException
            if ($exception instanceof ModuleResolutionException) {
                $exceptionRenderer->addCustomHtmlToHead(
                    '<style>
                        aside { display: none !important; }
                        main main:nth-child(2) { width: 100vw !important; }
                        section section header { width: 70vw !important; }
                    </style>'
                );
            }

            // General styling adjustments
            $exceptionRenderer->addCustomHtmlToHead(
                '<style>
                    .line-clamp-2 {-webkit-line-clamp: 10}
                </style>'
            );

            if ($stringFormat) {
                ob_start();
                $exceptionRenderer->renderException($exception);
                return ob_get_clean();
            }

            $exceptionRenderer->renderException($exception);
            return null;
        } else {
            // Fallback rendering if no exception renderer is available
            if ($stringFormat) {
                return $this->formatDebugError($exception);
            } else {
                echo $this->formatDebugError($exception);
                return null;
            }
        }
    }

    function formatDebugError(Throwable $exception): string
    {
        $timestamp = date('Y-m-d H:i:s');
        $exceptionClass = get_class($exception);
        $trace = $exception->getTraceAsString();
        $line = $exception->getLine();
        $file = $exception->getFile();
        $message = $exception->getMessage();
        $code = $exception->getCode();

        // Get server and request information
        $requestInfo = [
            'URL' => $_SERVER['REQUEST_URI'] ?? 'N/A',
            'Method' => $_SERVER['REQUEST_METHOD'] ?? 'N/A',
            'IP' => $_SERVER['REMOTE_ADDR'] ?? 'N/A',
            'User Agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'N/A',
            'Time' => $timestamp,
            'PHP Version' => PHP_VERSION,
        ];

        $template = '<!DOCTYPE html><html><head><title>Plugin Error</title><style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            body { background: #f5f5f5; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; line-height: 1.5; color: #333; }
            .error-container { max-width: 1200px; margin: 40px auto; padding: 20px; }
            .warning-banner { background: #fff3cd; border-left: 4px solid #ffc107; color: #856404; padding: 12px; margin-bottom: 20px; border-radius: 4px; display: flex; align-items: center; gap: 10px; }
            .error-header { background: #dc3545; color: white; padding: 20px; border-radius: 6px; margin-bottom: 20px; }
            .error-title { font-size: 24px; margin-bottom: 10px; }
            .error-subtitle { font-size: 16px; opacity: 0.9; }
            .error-section { background: white; border-radius: 6px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .section-header { background: #f8f9fa; padding: 12px 20px; border-bottom: 1px solid #dee2e6; font-weight: 600; }
            .section-content { padding: 20px; }
            .stack-trace { font-family: monospace; white-space: pre-wrap; word-break: break-word; background: #f8f9fa; padding: 15px; border-radius: 4px; font-size: 13px; }
            .exception-info { display: grid; grid-template-columns: 120px 1fr; gap: 10px; }
            .info-label { font-weight: 600; color: #666; }
            .code-context { background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; }
            .request-info { display: grid; grid-template-columns: 120px 1fr; gap: 10px; }
            .error-message { font-size: 16px; margin-bottom: 10px; }
            .trace-step { margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #eee; }
            .trace-step:last-child { border-bottom: none; }
        </style></head><body>
        <div class="error-container">
            <div class="warning-banner">
                <span style="font-size:24px">⚠️</span>
                <div>
                    <strong>Warning: Debug mode is enabled in production environment!</strong><br>
                    <small>This detailed error page is visible because debugging is enabled. For security reasons, disable debug mode in production.</small>
                </div>
            </div>
            
            <div class="error-header">
                <div class="error-title">' . htmlspecialchars($exceptionClass) . '</div>
                <div class="error-subtitle">' . htmlspecialchars($message) . '</div>
            </div>
    
            <div class="error-section">
                <div class="section-header">Exception Details</div>
                <div class="section-content exception-info">
                    <div class="info-label">Type:</div><div>' . htmlspecialchars($exceptionClass) . '</div>
                    <div class="info-label">Code:</div><div>' . htmlspecialchars($code) . '</div>
                    <div class="info-label">File:</div><div>' . htmlspecialchars($file) . '</div>
                    <div class="info-label">Line:</div><div>' . htmlspecialchars($line) . '</div>
                    <div class="info-label">Time:</div><div>' . htmlspecialchars($timestamp) . '</div>
                </div>
            </div>
    
            <div class="error-section">
                <div class="section-header">Stack Trace</div>
                <div class="section-content">
                    <div class="stack-trace">' . htmlspecialchars($trace) . '</div>
                </div>
            </div>
    
            <div class="error-section">
                <div class="section-header">Request Information</div>
                <div class="section-content request-info">';
        foreach ($requestInfo as $label => $value) {
            $template .= '<div class="info-label">' . htmlspecialchars($label) . ':</div><div>' . htmlspecialchars($value) . '</div>';
        }
        $template .= '</div>
            </div>';

        // Add $_GET, $_POST, and $_SERVER if you want (be careful with sensitive data)
        if (!empty($_GET)) {
            $template .= '<div class="error-section">
                <div class="section-header">$_GET Parameters</div>
                <div class="section-content">
                    <div class="code-context">' . htmlspecialchars(print_r($_GET, true)) . '</div>
                </div>
            </div>';
        }

        if (!empty($_POST)) {
            $template .= '<div class="error-section">
                <div class="section-header">$_POST Parameters</div>
                <div class="section-content">
                    <div class="code-context">' . htmlspecialchars(print_r($_POST, true)) . '</div>
                </div>
            </div>';
        }

        $template .= '</div></body></html>';

        return $template;
    }
}
