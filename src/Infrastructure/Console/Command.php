<?php
declare(strict_types=1);

namespace App\Infrastructure\Console;

abstract class Command
{
    protected string $name;
    protected string $description;
    protected array $arguments = [];
    protected array $options = [];

    public function __construct()
    {
        $this->configure();
    }

    abstract protected function configure(): void;
    abstract protected function execute(array $arguments, array $options): int;

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    protected function setName(string $name): void
    {
        $this->name = $name;
    }

    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    protected function addArgument(string $name, bool $required = false, string $description = ''): void
    {
        $this->arguments[$name] = [
            'required' => $required,
            'description' => $description
        ];
    }

    protected function addOption(string $name, ?string $shortcut = null, bool $required = false, string $description = ''): void
    {
        $this->options[$name] = [
            'shortcut' => $shortcut,
            'required' => $required,
            'description' => $description
        ];
    }

    public function run(array $argv): int
    {
        // Remove the script name and command name from argv
        $args = array_slice($argv, 2);
        $parsed = $this->parseArguments($args);
        return $this->execute($parsed['arguments'], $parsed['options']);
    }

    private function parseArguments(array $args): array
    {
        $arguments = [];
        $options = [];
        
        $argumentKeys = array_keys($this->arguments);
        $argumentIndex = 0;

        for ($i = 0; $i < count($args); $i++) {
            $arg = $args[$i];
            
            if (str_starts_with($arg, '--')) {
                $option = substr($arg, 2);
                $options[$option] = true;
                
                // Check if next argument is a value
                if (isset($args[$i + 1]) && !str_starts_with($args[$i + 1], '-')) {
                    $options[$option] = $args[$i + 1];
                    $i++;
                }
            } elseif (str_starts_with($arg, '-')) {
                $option = substr($arg, 1);
                $options[$option] = true;
                
                // Check if next argument is a value
                if (isset($args[$i + 1]) && !str_starts_with($args[$i + 1], '-')) {
                    $options[$option] = $args[$i + 1];
                    $i++;
                }
            } else {
                // It's a positional argument
                if (isset($argumentKeys[$argumentIndex])) {
                    $arguments[$argumentKeys[$argumentIndex]] = $arg;
                    $argumentIndex++;
                }
            }
        }

        return ['arguments' => $arguments, 'options' => $options];
    }

    protected function info(string $message): void
    {
        echo "\033[32m[INFO]\033[0m $message\n";
    }

    protected function error(string $message): void
    {
        echo "\033[31m[ERROR]\033[0m $message\n";
    }

    protected function warning(string $message): void
    {
        echo "\033[33m[WARNING]\033[0m $message\n";
    }

    protected function success(string $message): void
    {
        echo "\033[32m[SUCCESS]\033[0m $message\n";
    }
}
