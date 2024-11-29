<?php

namespace ModulesPress\Foundation\Http\Responses;

/**
 * Class HtmlResponse
 *
 * Represents an HTTP response with HTML content, status code, and headers.
 */
class HtmlResponse
{
    /**
     * Constructor for HtmlResponse.
     *
     * @param string $html The HTML content of the response.
     * @param int $statusCode The HTTP status code of the response.
     * @param array $headers An associative array of headers for the response.
     */
    public function __construct(
        private readonly string $html,
        private readonly int $statusCode,
        private readonly array $headers = []
    ) {}

    /**
     * Get the HTML content of the response.
     *
     * @return string The HTML content.
     */
    public function getHtml(): string
    {
        return $this->html;
    }

    /**
     * Get the HTTP status code of the response.
     *
     * @return int The status code.
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the headers of the response.
     *
     * @return array An associative array of headers.
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the HTML content of the response.
     *
     * @param string $html The new HTML content.
     * @return $this
     */
    public function setHtml(string $html): self
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Set the HTTP status code of the response.
     *
     * @param int $statusCode The new status code.
     * @return $this
     */
    public function setStatusCode(int $statusCode): self
    {
        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * Set the headers of the response.
     *
     * @param array $headers An associative array of new headers.
     * @return $this
     */
    public function setHeaders(array $headers): self
    {
        $this->headers = $headers;
        return $this;
    }
}
