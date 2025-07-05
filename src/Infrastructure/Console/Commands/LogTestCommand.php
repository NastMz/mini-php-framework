<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;
use App\Infrastructure\Logging\LoggerInterface;

/**
 * LogTestCommand
 *
 * Command to test logging functionality
 */
class LogTestCommand extends Command
{
    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('log:test');
        $this->setDescription('Test logging functionality by sending messages to both console and file');
    }

    public function execute(array $arguments, array $options): int
    {
        echo "Testing logging functionality...\n";
        
        // Test different log levels
        $this->logger->info('This is an info message', ['test' => 'value']);
        $this->logger->warn('This is a warning message', ['warning' => 'test']);
        $this->logger->error('This is an error message', ['error' => 'test']);
        $this->logger->debug('This is a debug message', ['debug' => 'test']);
        
        echo "Log messages sent. Check both console output and logs/app.log file.\n";
        
        return 0;
    }

    public function getDescription(): string
    {
        return 'Test logging functionality by sending messages to both console and file';
    }
}
