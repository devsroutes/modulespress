<?php

namespace ModulesPress\Core\ExceptionHandler;

use Exception;
use WP_REST_Response;

/**
 * FinalizedRESTResponse is a custom exception used to immediately send a finalized 
 * REST response to the client, bypassing the standard exception filters.
 * 
 * This exception is primarily used internally by ModulesPress to break the normal
 * exception flow and directly return a REST API response.
 * 
 * The `Send` method throws this exception with a `WP_REST_Response` as its payload.
 * The response will be sent directly to the client without going through the usual exception handling.
 */
final class FinalizedRESTResponse extends Exception
{
    /**
     * The WP_REST_Response that will be sent to the client.
     *
     * @var WP_REST_Response
     */
    public function __construct(private readonly WP_REST_Response $response) {}

    /**
     * Returns the wrapped WP_REST_Response.
     *
     * @return WP_REST_Response The REST response.
     */
    public function getResponse(): WP_REST_Response
    {
        return $this->response;
    }

    /**
     * Sends a finalized REST response by throwing the exception with the given response.
     *
     * @param WP_REST_Response $response The REST response to send.
     *
     * @return void
     * 
     * @throws FinalizedRESTResponse Throws the exception with the response.
     */
    public static function Send(WP_REST_Response $response): void
    {
        throw new self($response);
    }
}
