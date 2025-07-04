<?php

use App\Infrastructure\Persistence\MigrationInterface;
use PDO;

/**
 * Migration to create the rate_limits table.
 *
 * This table is used to store rate limiting information for IP addresses.
 * It tracks the start of the rate limit window and the number of requests made.
 */
class CreateRateLimitsTable implements MigrationInterface
{
    /**
     * Creates the rate_limits table.
     */
    public function up(PDO $pdo): void
    {
        $pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS rate_limits (
              ip             VARCHAR(45) PRIMARY KEY,
              window_start   INT NOT NULL,
              request_count  INT NOT NULL
            );
        SQL);
    }

    /**
     * Drops the rate_limits table.
     *
     * This method is used to revert the migration, removing the rate_limits table.
     */
    public function down(PDO $pdo): void
    {
        $pdo->exec("DROP TABLE IF EXISTS rate_limits;");
    }
}
