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
     * @return int The exit code of the command.
     */
    protected function execute(): int
    {
        $this->io->title('JWT Secret Generator');

        // Generate a secure random key (256 bits / 32 bytes)
        $secret = base64_encode(random_bytes(32));

        $this->io->success('JWT Secret generated successfully!');
        $this->io->newLine();
        
        $this->io->text('Add this line to your environment configuration:');
        $this->io->text("<info>JWT_SECRET={$secret}</info>");
        $this->io->newLine();
        
        $this->io->text('Or use it in your config/settings.php:');
        $this->io->text("<info>'jwt_secret' => '{$secret}',</info>");
        $this->io->newLine();
        
        $this->io->note('Keep this secret secure and never commit it to version control!');
        $this->io->warning('If you change this secret, all existing JWT tokens will become invalid.');

        return 0;
    }
}
