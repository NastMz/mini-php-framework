<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * MakeMigrationCommand class
 *
 * This command creates a new migration file in the migrations directory.
 */
class MakeMigrationCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('make:migration');
        $this->setDescription('Create a new migration file');
        $this->addArgument('name', true, 'The name of the migration');
    }

    /**
     * Execute the command to create a new migration.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['name'] ?? null;
        
        if (!$name) {
            $this->error('Migration name is required');
            return 1;
        }

        $timestamp = date('Ymd_His');
        $migrationName = $this->formatMigrationName($name);
        $className = $this->formatClassName($name);
        $fileName = "{$timestamp}_{$migrationName}.php";
        $filePath = __DIR__ . '/../../../../migrations/' . $fileName;

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $template = $this->getMigrationTemplate($className);
        file_put_contents($filePath, $template);
        
        $this->success("Migration '$fileName' created successfully at $filePath");
        return 0;
    }

    /**
     * Format the migration name to a valid file name.
     *
     * @param string $name The raw name of the migration.
     * @return string The formatted migration name.
     */
    private function formatMigrationName(string $name): string
    {
        $name = strtolower($name);
        $name = preg_replace('/[^a-z0-9_]/', '_', $name);
        $name = preg_replace('/_+/', '_', $name);
        return trim($name, '_');
    }

    /**
     * Format the migration name to a valid class name.
     *
     * @param string $name The raw name of the migration.
     * @return string The formatted class name.
     */
    private function formatClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    /**
     * Get the template for the migration class.
     *
     * @param string $className The name of the migration class.
     * @return string The template content.
     */
    private function getMigrationTemplate(string $className): string
    {
        return <<<PHP
            <?php
            declare(strict_types=1);

            use App\Infrastructure\Persistence\MigrationInterface;
            use PDO;

            class {$className} implements MigrationInterface
            {
                public function up(PDO \$pdo): void
                {
                    \$sql = "
                        -- TODO: Add your migration SQL here
                        -- Example:
                        -- CREATE TABLE example (
                        --     id INT AUTO_INCREMENT PRIMARY KEY,
                        --     name VARCHAR(255) NOT NULL,
                        --     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        -- );
                    ";
                    
                    \$pdo->exec(\$sql);
                }

                public function down(PDO \$pdo): void
                {
                    \$sql = "
                        -- TODO: Add your rollback SQL here
                        -- Example:
                        -- DROP TABLE IF EXISTS example;
                    ";
                    
                    \$pdo->exec(\$sql);
                }

                public function getVersion(): string
                {
                    return basename(__FILE__, '.php');
                }
            }
        PHP;
    }
}
