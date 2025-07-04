<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

/**
 * Enum HttpMethod
 *
 * Represents the HTTP methods used in routing.
 */
enum HttpMethod: string
{
    case GET     = 'GET';
    case POST    = 'POST';
    case PUT     = 'PUT';
    case PATCH   = 'PATCH';
    case DELETE  = 'DELETE';
    case OPTIONS = 'OPTIONS';
}
