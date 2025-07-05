<?php
declare(strict_types=1);

namespace App\Infrastructure\Routing;

use RuntimeException;

/**
 * MethodNotAllowedException
 *
 * Exception thrown when a HTTP method is not supported.
 * This should result in a 405 Method Not Allowed response.
 */
class MethodNotAllowedException extends RuntimeException
{
    public function __construct(string $method)
    {
        parent::__construct("HTTP method not allowed: {$method}");
    }
}
