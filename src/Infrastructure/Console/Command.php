<?php
declare(strict_types=1);

namespace App\Infrastructure\Console;

/**
 * Abstract class Command
 *
 * This class serves as a base for all console commands in the application.
 * It provides methods to configure the command, execute it, and handle arguments and options.
 */
abstract class Command
{
    protected string $name;
    protected string $description;
    protected array $arguments = [];
    protected array $options = [];

    /**
     * Constructor to initialize the command.
     * It calls the configure method to set up the command's properties.
     */
    public function __construct()
    {
        $this->configure();
    }

    /**
     * Configure the command options and description.
     * This method should be implemented in subclasses to define the command's behavior.
     */
    abstract protected function configure(): void;

    /**
     * Execute the command with the provided arguments and options.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    abstract protected function execute(array $arguments, array $options): int;

    /**
     * Get the command name.
     *
     * @return string The name of the command.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get the command description.
     *
     * @return string The description of the command.
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get the command arguments.
     *
     * @return array The arguments of the command.
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the command options.
     *
     * @return array The options of the command.
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Set the command name.
     *
     * @param string $name The name of the command.
     */
    protected function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Set the command description.
     *
     * @param string $description The description of the command.
     */
    protected function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * Add an argument to the command.
     *
     * @param string $name The name of the argument.
     * @param bool $required Whether the argument is required.
     * @param string $description A description of the argument.
     */
    protected function addArgument(string $name, bool $required = false, string $description = ''): void
    {
        $this->arguments[$name] = [
            'required' => $required,
            'description' => $description
        ];
    }

    /**
     * Add an option to the command.
     *
     * @param string $name The name of the option.
     * @param string|null $shortcut The shortcut for the option (optional).
     * @param bool $required Whether the option is required.
     * @param string $description A description of the option.
     */
    protected function addOption(string $name, ?string $shortcut = null, bool $required = false, string $description = ''): void
    {
        $this->options[$name] = [
            'shortcut' => $shortcut,
            'required' => $required,
            'description' => $description
        ];
    }

    /**
     * Run the command with the provided arguments.
     *
     * @param array $argv The command line arguments.
     * @return int The exit code of the command.
     */
    public function run(array $argv): int
    {
        // Remove the script name and command name from argv
        $args = array_slice($argv, 2);
        $parsed = $this->parseArguments($args);
        return $this->execute($parsed['arguments'], $parsed['options']);
    }

    /**
     * Parse the command line arguments and options.
     *
     * @param array $args The command line arguments.
     * @return array An associative array containing 'arguments' and 'options'.
     */
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

    /**
     * Print an informational message to the console.
     *
     * @param string $message The message to print.
     */
    protected function info(string $message): void
    {
        echo "\033[32m[INFO]\033[0m $message\n";
    }

    /**
     * Print an error message to the console.
     *
     * @param string $message The error message to print.
     */
    protected function error(string $message): void
    {
        echo "\033[31m[ERROR]\033[0m $message\n";
    }

    /**
     * Print a warning message to the console.
     *
     * @param string $message The warning message to print.
     */
    protected function warning(string $message): void
    {
        echo "\033[33m[WARNING]\033[0m $message\n";
    }

    /**
     * Print a success message to the console.
     *
     * @param string $message The success message to print.
     */
    protected function success(string $message): void
    {
        echo "\033[32m[SUCCESS]\033[0m $message\n";
    }
}
