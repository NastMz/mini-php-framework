<?php
declare(strict_types=1);

namespace App\Presentation\Controller\Api;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;

/**
 * API Controller
 * 
 * Handles API requests
 */
class ApiController
{
    /**
     * API status endpoint
     */
    public function status(RequestInterface $request): ResponseInterface
    {
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'status' => 'ok',
                'version' => '1.0.0',
                'timestamp' => date('c'),
                'message' => 'API is running'
            ]));
    }

    /**
     * API info endpoint
     */
    public function info(RequestInterface $request): ResponseInterface
    {
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode([
                'framework' => 'Mini Framework PHP',
                'version' => '1.0.0',
                'features' => [
                    'dependency_injection' => 'PSR-11',
                    'middleware' => 'PSR-15 compatible',
                    'routing' => 'HTTP method routing',
                    'security' => 'CORS, CSP, Security Headers'
                ],
                'endpoints' => [
                    'GET /api/status' => 'API status',
                    'GET /api/info' => 'API information'
                ]
            ]));
    }
}
