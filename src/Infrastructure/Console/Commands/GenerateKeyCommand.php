<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class GenerateKeyCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('key:generate');
        $this->setDescription('Generate a new application key');
        $this->addOption('show', 's', false, 'Display the key instead of modifying files');
    }

    protected function execute(array $arguments, array $options): int
    {
        $key = $this->generateKey();
        
        if (isset($options['show']) || isset($options['s'])) {
            $this->info("Application key: $key");
            return 0;
        }

        $envFile = __DIR__ . '/../../../../.env';
        $envExampleFile = __DIR__ . '/../../../../.env.example';
        
        if (file_exists($envFile)) {
            $this->updateEnvFile($envFile, $key);
            $this->success("Application key updated in .env file");
        } else {
            $this->warning(".env file not found");
        }

        if (file_exists($envExampleFile)) {
            $this->updateEnvFile($envExampleFile, $key);
            $this->info("Application key updated in .env.example file");
        }

        $this->info("New application key: $key");
        return 0;
    }

    private function generateKey(): string
    {
        return base64_encode(random_bytes(32));
    }

    private function updateEnvFile(string $file, string $key): void
    {
        $content = file_get_contents($file);
        
        if (preg_match('/^APP_KEY=.*$/m', $content)) {
            $content = preg_replace('/^APP_KEY=.*$/m', "APP_KEY=$key", $content);
        } else {
            $content .= "\nAPP_KEY=$key\n";
        }
        
        file_put_contents($file, $content);
    }
}
