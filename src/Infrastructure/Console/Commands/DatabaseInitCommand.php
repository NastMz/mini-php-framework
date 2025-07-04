<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Database\DatabaseHelper;
use App\Infrastructure\Config\EnvLoader;

/**
 * DatabaseInitCommand class
 *
 * This command initializes the database by creating the necessary file and directories.
 */
class DatabaseInitCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('db:init');
        $this->setDescription('Initialize database (create file and directories)');
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
        $this->info('🔧 Initializing database...');
        
        try {
            // Load settings
            $settings = require __DIR__ . '/../../../../config/settings.php';
            $dbConfig = DatabaseHelper::getConfig($settings['database']);
            
            // Create PDO connection (this will create the database file)
            $pdo = DatabaseHelper::createPdo($dbConfig);
            
            // Test connection
            $stmt = $pdo->query('SELECT 1');
            if ($stmt->fetch()) {
                $this->success('✅ Database initialized successfully');
                
                // Show database info
                $dsn = $dbConfig['dsn'];
                if (str_starts_with($dsn, 'sqlite:')) {
                    $dbPath = str_replace('sqlite:', '', $dsn);
                    $this->info("📁 Database location: {$dbPath}");
                } else {
                    $this->info("🔗 Database DSN: {$dsn}");
                }
                
                return 0;
            } else {
                $this->error('❌ Database connection test failed');
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('❌ Database initialization failed: ' . $e->getMessage());
            return 1;
        }
    }
}
