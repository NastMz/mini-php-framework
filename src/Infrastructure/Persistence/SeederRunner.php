<?php
declare(strict_types=1);

namespace App\Infrastructure\Persistence;

use PDO;

/**
 * SeederRunner
 *
 * A simple seeder runner that applies database seeders from a specified directory.
 * It loads each seeder class and runs it if it implements the SeederInterface.
 */
class SeederRunner
{
    /**
     * SeederRunner constructor.
     *
     * @param PDO $pdo PDO instance for database connection
     * @param string $seedersDir Directory containing seeder files
     */
    public function __construct(private PDO $pdo, private string $seedersDir) {}

    /**
     * Run all seeders in the specified directory.
     * It checks the seeders directory for PHP files, loads them,
     * and runs each seeder that implements the SeederInterface.
     */
    public function runAll(): void
    {
        $files = glob($this->seedersDir . '/*.php');
        sort($files);

        foreach ($files as $file) {
            require $file;
            $class = pathinfo($file, PATHINFO_FILENAME);
            if (! class_exists($class)) {
                continue;
            }
            $seeder = new $class();
            if (! $seeder instanceof SeederInterface) {
                continue;
            }
            echo "Running seeder: {$class}\n";
            $seeder->run($this->pdo);
        }
    }
}
