<?php
declare(strict_types=1);

namespace App\Application\Query\Handlers;

use App\Application\Query\QueryHandlerInterface;
use App\Application\Query\QueryInterface;
use App\Application\Query\GetSystemHealthQuery;
use App\Infrastructure\Health\HealthCheckService;

/**
 * GetSystemHealthQueryHandler
 *
 * Handles queries for system health status.
 */
class GetSystemHealthQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private HealthCheckService $healthService
    ) {}

    public function handle(QueryInterface $query): mixed
    {
        if (!$query instanceof GetSystemHealthQuery) {
            throw new \InvalidArgumentException('Expected GetSystemHealthQuery');
        }

        // Use existing health check service
        $healthReport = $this->healthService->checkAll();

        return [
            'status' => $healthReport['database'] ? 'healthy' : 'unhealthy',
            'checks' => $healthReport,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
