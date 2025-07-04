<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

/**
 * SeederInterface
 *
 * Interface for database seeders.
 * Defines a method for running seed operations on the database.
 */
interface SeederInterface
{
    /**
     * Run the seeder.
     *
     * This method should contain the logic to seed the database with initial data.
     *
     * @param PDO $pdo PDO instance for database connection
     */
    public function run(PDO $pdo): void;
}
