<?php

namespace ModulesPress\Foundation\Http\Contracts;

use Attribute;
use WP_REST_Request;
use WP_REST_Response;

/**
 * Interface Middleware
 *
 * This interface defines a contract for middleware that operates on REST API requests and responses.
 * Middleware can either modify the incoming request or the outgoing response. If a `WP_REST_Response` is returned,
 * it halts the chain and sends the response to the client. If a `WP_REST_Request` is returned, the chain continues
 * to the next middleware or the final handler.
 *
 * The middleware can be applied to requests before they are processed or to responses before they are returned to the client.
 */
#[Attribute(Attribute::TARGET_CLASS)]
interface Middleware
{
    /**
     * Applies middleware to the given request and response.
     *
     * This method allows middleware to modify the incoming request (`WP_REST_Request`) or outgoing response
     * (`WP_REST_Response`). If a `WP_REST_Response` is returned, it will be sent directly to the client,
     * and no further middleware will be executed. If a `WP_REST_Request` is returned, the chain continues to the next
     * middleware or handler.
     *
     * @param WP_REST_Request $req The incoming REST request to be processed.
     * @param WP_REST_Response $res The response that will be sent to the client.
     *
     * @return WP_REST_Request|WP_REST_Response The modified request, to continue the chain, or a response to
     *                                          send directly to the client.
     */
    public function use(WP_REST_Request $req, WP_REST_Response $res): WP_REST_Request | WP_REST_Response;
}
