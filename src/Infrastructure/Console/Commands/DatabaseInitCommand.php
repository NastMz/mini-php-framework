<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Database\DatabaseHelper;
use PDO;

/**
 * Class DatabaseInitCommand
 *
 * This command initializes the database, creating it if it doesn't exist.
 */
class DatabaseInitCommand extends Command
{
    /**
     * Configure the command options and description.
     */
    protected function configure(): void
    {
        $this->setName('db:init');
        $this->setDescription('Initialize the database (create if not exists)');
    }

    /**
     * Execute the command to initialize the database.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        try {
            // Load the complete bootstrap container
            $container = require_once __DIR__ . '/../../../../bootstrap/dependencies.php';
            
            $this->info('Initializing database...');
            
            // Get PDO connection (this will auto-create the database if needed)
            $pdo = $container->get(PDO::class);
            $settings = $container->get('settings');
            
            if ($pdo) {
                $this->success('Database initialized successfully');
                $this->info('Database location: ' . $settings['database']['database']);
            } else {
                $this->error('Failed to initialize database');
                return 1;
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Database initialization failed: ' . $e->getMessage());
            return 1;
        }
    }
}
