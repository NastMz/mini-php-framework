<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Health\HealthCheckService;

/**
 * HealthController
 *
 * Provides an endpoint to check the health status of the application.
 * This controller checks the database connection and returns a JSON report.
 */
class HealthController
{
    /**
     * @param HealthCheckService $health Service to perform health checks
     */
    public function __construct(private HealthCheckService $health) {}

    /**
     * Handles the health check request.
     *
     * @return Response JSON response with health status
     */
    public function status(): ResponseInterface
    {
        $report = $this->health->checkAll();
        $ok     = $report['database'] === true;
        return (new Response())
            ->withStatus($ok ? 200 : 500)
            ->withHeader('Content-Type','application/json')
            ->write(json_encode($report));
    }
}
