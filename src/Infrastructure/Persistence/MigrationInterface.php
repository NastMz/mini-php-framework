<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

/**
 * MigrationInterface
 *
 * Interface for database migrations.
 * Defines methods for applying and reverting migrations.
 */
interface MigrationInterface
{
    /**
     * Apply the migration.
     *
     * @param PDO $pdo PDO instance for database connection
     */
    public function up(PDO $pdo): void;

    /**
     * Revert the migration.
     *
     * @param PDO $pdo PDO instance for database connection
     */
    public function down(PDO $pdo): void;
}
