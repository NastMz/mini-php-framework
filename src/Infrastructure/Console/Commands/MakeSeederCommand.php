<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * MakeSeederCommand class
 *
 * This command creates a new seeder class in the application.
 */
class MakeSeederCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('make:seeder');
        $this->setDescription('Create a new seeder class');
        $this->addArgument('name', true, 'The name of the seeder');
    }

    /**
     * Execute the command to create a new seeder.
     *
     * @param array $arguments The command arguments.
     * @param array $options The command options.
     * @return int The exit code of the command.
     */
    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['name'] ?? null;
        
        if (!$name) {
            $this->error('Seeder name is required');
            return 1;
        }

        $className = $this->formatClassName($name);
        $fileName = $className . '.php';
        $filePath = __DIR__ . '/../../../../seeders/' . $fileName;

        if (file_exists($filePath)) {
            $this->error("Seeder '$className' already exists");
            return 1;
        }

        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $template = $this->getSeederTemplate($className);
        file_put_contents($filePath, $template);
        
        $this->success("Seeder '$className' created successfully at $filePath");
        return 0;
    }

    /**
     * Format the seeder name to a valid class name.
     *
     * @param string $name The raw name of the seeder.
     * @return string The formatted class name.
     */
    private function formatClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        $name = str_replace(' ', '', $name);
        
        if (!str_ends_with($name, 'Seeder')) {
            $name .= 'Seeder';
        }
        
        return $name;
    }

    /**
     * Get the template for the seeder class.
     *
     * @param string $className The name of the seeder class.
     * @return string The template content.
     */
    private function getSeederTemplate(string $className): string
    {
        return <<<PHP
            <?php
            declare(strict_types=1);

            use App\Infrastructure\Persistence\SeederInterface;
            use PDO;

            class {$className} implements SeederInterface
            {
                public function run(PDO \$pdo): void
                {
                    // TODO: Implement your seeder logic here
                    // Example:
                    // \$stmt = \$pdo->prepare("INSERT INTO users (name, email) VALUES (?, ?)");
                    // \$stmt->execute(['John Doe', 'john@example.com']);
                    // \$stmt->execute(['Jane Smith', 'jane@example.com']);
                    
                    echo "Running {$className}...\n";
                }

                public function getName(): string
                {
                    return '{$className}';
                }
            }
        PHP;
    }
}
