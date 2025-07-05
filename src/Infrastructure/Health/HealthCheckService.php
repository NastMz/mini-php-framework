<?php
declare(strict_types=1);

namespace App\Infrastructure\Health;

use PDO;

/**
 * HealthCheckService
 *
 * Provides basic health checks for the application.
 * This service checks the database connection and uptime.
 */
class HealthCheckService
{
    /**
     * @param PDO $pdo Database connection instance
     */
    public function __construct(private PDO $pdo) {}

    /**
     * @return array<string,bool|int>  e.g. ['database'=>true, 'uptime'=>123]
     */
    public function checkAll(): array
    {
        $status = [
            'database' => false,
            // uptime in seconds since PHP started
            'uptime'   => (int) (microtime(true) - ($_SERVER['REQUEST_TIME_FLOAT'] ?? time()))
        ];

        try {
            // simple DB check
            $this->pdo->query('SELECT 1');
            $status['database'] = true;
        } catch (\Throwable) {
            $status['database'] = false;
        }

        return $status;
    }
}
