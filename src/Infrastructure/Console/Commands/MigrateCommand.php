<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\DI\Container;
use App\Infrastructure\Persistence\MigrationRunner;
use PDO;

/**
 * Class MigrateCommand
 *
 * This command handles database migrations, allowing you to run, rollback, or fresh migrations.
 */
class MigrateCommand extends Command
{
    /**
     * Configure the command options and description.
     */
    protected function configure(): void
    {
        $this->setName('migrate');
        $this->setDescription('Run database migrations');
        $this->addOption('rollback', 'r', false, 'Rollback the last migration');
        $this->addOption('fresh', 'f', false, 'Drop all tables and re-run migrations');
    }

    /**
     * Execute the command to run migrations.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        try {
            $settings = require __DIR__ . '/../../../../config/settings.php';
            $container = Container::build($settings);
            $pdo = $container->get(PDO::class);
            
            $migrationRunner = new MigrationRunner($pdo, __DIR__ . '/../../../../migrations');
            
            if (isset($options['fresh']) || isset($options['f'])) {
                $this->info('Running fresh migrations...');
                $migrationRunner->fresh();
                $this->success('Fresh migrations completed');
            } elseif (isset($options['rollback']) || isset($options['r'])) {
                $this->info('Rolling back last migration...');
                $migrationRunner->rollback();
                $this->success('Migration rolled back');
            } else {
                $this->info('Running pending migrations...');
                $migrationRunner->runPending();
                $this->success('Migrations completed');
            }
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
}
