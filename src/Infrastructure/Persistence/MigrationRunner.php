<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

/**
 * MigrationRunner
 *
 * A simple migration runner that applies database migrations from a specified directory.
 * It checks for already applied migrations and only runs those that are pending.
 */
class MigrationRunner
{
    private PDO    $pdo;
    private string $dir;

    /**
     * MigrationRunner constructor.
     *
     * @param PDO $pdo PDO instance for database connection
     * @param string $migrationsDir Directory containing migration files
     */
    public function __construct(PDO $pdo, string $migrationsDir)
    {
        $this->pdo = $pdo;
        $this->dir = $migrationsDir;
        $this->ensureMigrationsTable();
    }

    /**
     * Ensure the migrations table exists.
     * Creates the table if it does not already exist.
     */
    private function ensureMigrationsTable(): void
    {
        $this->pdo->exec(<<<'SQL'
            CREATE TABLE IF NOT EXISTS migrations (
              name VARCHAR(255) PRIMARY KEY,
              applied_at DATETIME NOT NULL
            );
        SQL);
    }

    /** @return string[] */
    public function getApplied(): array
    {
        $stmt = $this->pdo->query("SELECT name FROM migrations;");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Run all pending migrations.
     * It checks the migrations directory for PHP files, loads them,
     * and applies them if they have not been applied yet.
     */
    public function runPending(): void
    {
        $applied = array_flip($this->getApplied());
        $files   = glob($this->dir . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file, '.php');
            if (isset($applied[$name])) {
                continue;
            }

            require $file;
            $className = $this->toClassName($name);
            /** @var MigrationInterface $migration */
            $migration = new $className();

            $this->pdo->beginTransaction();
            $migration->up($this->pdo);
            $stmt = $this->pdo->prepare(
                "INSERT INTO migrations(name, applied_at) VALUES(:name, NOW());"
            );
            $stmt->execute(['name' => $name]);
            $this->pdo->commit();

            echo "Applied migration: {$name}\n";
        }
    }

    /**
     * Transform a file name into a class name.
     * This converts snake_case or similar file names into PascalCase.
     * For example, "20231001_create_users_table.php" becomes "CreateUsersTable".
     *
     * @param string $fileName The file name to convert
     * @return string The converted class name
     */
    private function toClassName(string $fileName): string
    {
        // strip leading digits/underscores, then snake_case → PascalCase
        $base = preg_replace('/^[0-9_]+/', '', $fileName);
        return implode('', array_map('ucfirst', explode('_', $base)));
    }
}
