<?php
declare(strict_types=1);

namespace App\Infrastructure\Console;

/**
 * Class Application
 *
 * This class represents the console application that manages commands and their execution.
 */
class Application
{
    private array $commands = [];
    private string $name;
    private string $version;

    /**
     * Application constructor.
     *
     * @param string $name The name of the application.
     * @param string $version The version of the application.
     */
    public function __construct(string $name = 'MiniFramework CLI', string $version = '1.0.0')
    {
        $this->name = $name;
        $this->version = $version;
    }

    /**
     * Add a command to the application.
     *
     * @param Command $command The command to add.
     */
    public function addCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    /**
     * Run the application with the provided arguments.
     *
     * @param array $argv The command line arguments.
     * @return int The exit code of the application.
     */
    public function run(array $argv): int
    {
        $exitCode = 0;
        
        if (count($argv) < 2) {
            $this->showHelp();
        } elseif ($this->isHelpCommand($argv[1])) {
            $this->showHelp();
        } elseif ($this->isVersionCommand($argv[1])) {
            $this->showVersion();
        } elseif (!isset($this->commands[$argv[1]])) {
            echo "\033[31mCommand '{$argv[1]}' not found.\033[0m\n\n";
            $this->showHelp();
            $exitCode = 1;
        } else {
            $command = $this->commands[$argv[1]];
            $exitCode = $command->run($argv);
        }
        
        return $exitCode;
    }
    
    /**
     * Check if the command is a help command.
     *
     * @param string $commandName The command name to check.
     * @return bool True if it's a help command, false otherwise.
     */
    private function isHelpCommand(string $commandName): bool
    {
        return in_array($commandName, ['help', '--help', '-h']);
    }
    
    /**
     * Check if the command is a version command.
     *
     * @param string $commandName The command name to check.
     * @return bool True if it's a version command, false otherwise.
     */
    private function isVersionCommand(string $commandName): bool
    {
        return in_array($commandName, ['version', '--version', '-V']);
    }

    /**
     * Show the version of the application.
     */
    private function showVersion(): void
    {
        echo "{$this->name} {$this->version}\n";
    }

    /**
     * Show the help message for the application.
     */
    private function showHelp(): void
    {
        echo "\033[32m{$this->name} {$this->version}\033[0m\n\n";
        echo "Usage:\n";
        echo "  command [options] [arguments]\n\n";
        echo "Available commands:\n";

        foreach ($this->commands as $command) {
            echo sprintf("  \033[33m%-20s\033[0m %s\n", $command->getName(), $command->getDescription());
        }

        echo "\nGlobal options:\n";
        echo "  \033[33m-h, --help\033[0m     Show this help message\n";
        echo "  \033[33m-V, --version\033[0m  Show version information\n";
    }
}
