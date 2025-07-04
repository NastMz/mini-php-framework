<?php
declare(strict_types=1);

namespace App\Infrastructure\Console;

class Application
{
    private array $commands = [];
    private string $name;
    private string $version;

    public function __construct(string $name = 'MiniFramework CLI', string $version = '1.0.0')
    {
        $this->name = $name;
        $this->version = $version;
    }

    public function addCommand(Command $command): void
    {
        $this->commands[$command->getName()] = $command;
    }

    public function run(array $argv): int
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return 0;
        }

        $commandName = $argv[1];

        if ($commandName === 'help' || $commandName === '--help' || $commandName === '-h') {
            $this->showHelp();
            return 0;
        }

        if ($commandName === 'version' || $commandName === '--version' || $commandName === '-V') {
            $this->showVersion();
            return 0;
        }

        if (!isset($this->commands[$commandName])) {
            echo "\033[31mCommand '$commandName' not found.\033[0m\n\n";
            $this->showHelp();
            return 1;
        }

        $command = $this->commands[$commandName];
        return $command->run($argv);
    }

    private function showVersion(): void
    {
        echo "{$this->name} {$this->version}\n";
    }

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
