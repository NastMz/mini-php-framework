<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * GenerateJwtSecretCommand
 *
 * Generates a secure JWT secret key for the application.
 */
class GenerateJwtSecretCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('jwt:secret');
        $this->setDescription('Generate a secure JWT secret key');
    }

    /**
     * Execute the command.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $this->info('JWT Secret Generator');
        $this->info('===================');
        echo "\n";

        // Generate a secure random key (256 bits / 32 bytes)
        $secret = base64_encode(random_bytes(32));

        $this->success('JWT Secret generated successfully!');
        echo "\n";
        
        $this->info('Add this line to your environment configuration:');
        $this->info("JWT_SECRET={$secret}");
        echo "\n";
        
        $this->info('Or use it in your config/settings.php:');
        $this->info("'jwt_secret' => '{$secret}',");
        echo "\n";
        
        $this->warning('Keep this secret secure and never commit it to version control!');
        $this->warning('If you change this secret, all existing JWT tokens will become invalid.');

        return 0;
    }
}
