<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class ServeCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('serve');
        $this->setDescription('Start the development server');
        $this->addOption('host', 'H', false, 'The host to serve on (default: localhost)');
        $this->addOption('port', 'p', false, 'The port to serve on (default: 8000)');
    }

    protected function execute(array $arguments, array $options): int
    {
        $host = $options['host'] ?? $options['H'] ?? 'localhost';
        $port = $options['port'] ?? $options['p'] ?? '8000';
        
        $documentRoot = __DIR__ . '/../../../../public';
        
        if (!is_dir($documentRoot)) {
            $this->error('Public directory not found');
            return 1;
        }

        $this->info("Starting development server on http://{$host}:{$port}");
        $this->info("Document root: {$documentRoot}");
        $this->info("Press Ctrl+C to stop the server");

        $command = sprintf(
            'php -S %s:%s -t %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($documentRoot)
        );

        // Execute the command
        passthru($command, $exitCode);
        
        return $exitCode;
    }
}
