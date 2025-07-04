<?php
declare(strict_types=1);

namespace App\Infrastructure\Middleware;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\ResponseInterface;

/**
 * Interface RequestHandlerInterface
 *
 * Represents a request handler in the application's middleware pipeline.
 * It processes incoming requests and returns responses.
 */
interface RequestHandlerInterface
{
    /**
     * Handle the request and return a response.
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     */
    public function handle(RequestInterface $request): ResponseInterface;
}
