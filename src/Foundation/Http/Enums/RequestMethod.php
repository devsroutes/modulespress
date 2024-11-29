<?php 

namespace ModulesPress\Foundation\Http\Enums;

/**
 * Enum RequestMethod
 * 
 * Defines the HTTP request methods used in the plugin.
 * Each case corresponds to a different type of HTTP request method.
 */
enum RequestMethod: string
{
    /**
     * HTTP GET method.
     */
    case GET = "GET";

    /**
     * HTTP POST method.
     */
    case POST = "POST";

    /**
     * HTTP PUT method.
     */
    case PUT = "PUT";

    /**
     * HTTP DELETE method.
     */
    case DELETE = "DELETE";

    /**
     * HTTP OPTIONS method.
     */
    case OPTIONS = "OPTIONS";

    /**
     * HTTP HEAD method.
     */
    case HEAD = "HEAD";

    /**
     * HTTP PATCH method.
     */
    case PATCH = "PATCH";
}
