<?php

namespace ModulesPress\Foundation\Http\Responses;

/**
 * Class JsonResponse
 *
 * Represents an HTTP response with JSON data, a status code, and headers.
 */
class JsonResponse
{
    /**
     * Constructor for JsonResponse.
     *
     * @param array $data The data to be returned in the JSON response.
     * @param int $statusCode The HTTP status code for the response.
     * @param array $headers An optional associative array of headers for the response.
     */
    public function __construct(
        private readonly array $data,
        private readonly int $statusCode,
        private readonly array $headers = []
    ) {}

    /**
     * Get the data of the JSON response.
     *
     * @return array The data being returned in the JSON response.
     */
    public function getData(): array
    {
        return $this->data;
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
     * Set the data for the JSON response.
     *
     * @param array $data The new data to be included in the response.
     * @return $this
     */
    public function setData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Set the HTTP status code for the JSON response.
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
     * Set the headers for the JSON response.
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
