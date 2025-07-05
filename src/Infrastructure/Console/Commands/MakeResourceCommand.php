<?php
declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

/**
 * MakeResourceCommand
 *
 * Command to create complete CRUD resources with automatic routing and validation
 */
class MakeResourceCommand extends Command
{
    /**
     * Configure the command options and arguments.
     */
    protected function configure(): void
    {
        $this->setName('make:resource');
        $this->setDescription('Create a complete CRUD resource (Model, Controller, Migration, Seeder)');
        $this->addArgument('name', true, 'The name of the resource (e.g., User, Product)');
        $this->addOption('api', false, 'Generate API resource only');
        $this->addOption('model', false, 'Generate model only');
        $this->addOption('controller', false, 'Generate controller only');
        $this->addOption('migration', false, 'Generate migration only');
        $this->addOption('seeder', false, 'Generate seeder only');
    }

    /**
     * Execute the command to create a complete resource.
     */
    protected function execute(array $arguments, array $options): int
    {
        $name = $arguments['name'] ?? null;
        
        if (!$name) {
            $this->error('Resource name is required');
            return 1;
        }

        $resourceName = $this->formatClassName($name);
        
        $this->info("Creating resource: {$resourceName}");
        
        // Generate components based on options
        $generateAll = !($options['model'] || $options['controller'] || $options['migration'] || $options['seeder']);
        $apiOnly = $options['api'] ?? false;
        
        if ($generateAll || $options['model']) {
            $this->generateModel($resourceName);
        }
        
        if ($generateAll || $options['controller']) {
            $this->generateController($resourceName, $apiOnly);
        }
        
        if ($generateAll || $options['migration']) {
            $this->generateMigration($resourceName);
        }
        
        if ($generateAll || $options['seeder']) {
            $this->generateSeeder($resourceName);
        }

        $this->success("Resource '{$resourceName}' created successfully!");
        
        // Show next steps
        $this->info("\nNext steps:");
        $this->info("1. Edit the migration file to define your table structure");
        $this->info("2. Run: php bin/console migrate");
        $this->info("3. Edit the seeder to add sample data");
        $this->info("4. Run: php bin/console seed");
        $this->info("5. Edit the controller to implement your business logic");
        
        return 0;
    }

    /**
     * Generate model class
     */
    private function generateModel(string $name): void
    {
        $modelTemplate = $this->getModelTemplate($name);
        $filePath = __DIR__ . '/../../../../src/Domain/Model/' . $name . '.php';
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, $modelTemplate);
        $this->success("✓ Model created: {$filePath}");
    }

    /**
     * Generate controller class
     */
    private function generateController(string $name, bool $apiOnly = false): void
    {
        $controllerTemplate = $this->getControllerTemplate($name, $apiOnly);
        $fileName = $name . 'Controller.php';
        $filePath = __DIR__ . '/../../../../src/Presentation/Controller/' . $fileName;
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, $controllerTemplate);
        $this->success("✓ Controller created: {$filePath}");
    }

    /**
     * Generate migration
     */
    private function generateMigration(string $name): void
    {
        $tableName = $this->toSnakeCase($name) . 's';
        $migrationName = 'create_' . $tableName . '_table';
        $timestamp = date('Ymd_His');
        $className = 'Create' . $name . 'sTable';
        
        $migrationTemplate = $this->getMigrationTemplate($className, $tableName);
        $fileName = $timestamp . '_' . $migrationName . '.php';
        $filePath = __DIR__ . '/../../../../migrations/' . $fileName;
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, $migrationTemplate);
        $this->success("✓ Migration created: {$filePath}");
    }

    /**
     * Generate seeder
     */
    private function generateSeeder(string $name): void
    {
        $seederTemplate = $this->getSeederTemplate($name);
        $fileName = $name . 'Seeder.php';
        $filePath = __DIR__ . '/../../../../seeders/' . $fileName;
        
        if (!is_dir(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }
        
        file_put_contents($filePath, $seederTemplate);
        $this->success("✓ Seeder created: {$filePath}");
    }

    /**
     * Get model template
     */
    private function getModelTemplate(string $name): string
    {
        $tableName = $this->toSnakeCase($name) . 's';
        
        return <<<PHP
<?php
declare(strict_types=1);

namespace App\Domain\Model;

use App\Infrastructure\Serialization\Attributes\JsonSerializable;
use App\Infrastructure\Serialization\Attributes\JsonProperty;
use App\Infrastructure\Serialization\Attributes\JsonIgnore;
use App\Infrastructure\Persistence\Mapping\Table;
use App\Infrastructure\Persistence\Mapping\Column;
use DateTime;

/**
 * {$name} model
 */
#[Table(name: '{$tableName}')]
#[JsonSerializable(camelCase: true)]
class {$name}
{
    #[Column(name: 'id', id: true, auto: true)]
    private ?int \$id = null;

    #[Column(name: 'name')]
    private string \$name;

    #[Column(name: 'created_at')]
    #[JsonProperty(name: 'created_at', format: 'datetime')]
    private DateTime \$createdAt;

    #[Column(name: 'updated_at')]
    #[JsonProperty(name: 'updated_at', format: 'datetime')]
    private DateTime \$updatedAt;

    public function __construct(string \$name)
    {
        \$this->name = \$name;
        \$this->createdAt = new DateTime();
        \$this->updatedAt = new DateTime();
    }

    // Getters
    public function getId(): ?int
    {
        return \$this->id;
    }

    public function getName(): string
    {
        return \$this->name;
    }

    public function getCreatedAt(): DateTime
    {
        return \$this->createdAt;
    }

    public function getUpdatedAt(): DateTime
    {
        return \$this->updatedAt;
    }

    // Setters
    public function setId(?int \$id): void
    {
        \$this->id = \$id;
    }

    public function setName(string \$name): void
    {
        \$this->name = \$name;
        \$this->updatedAt = new DateTime();
    }
}
PHP;
    }

    /**
     * Get controller template
     */
    private function getControllerTemplate(string $name, bool $apiOnly = false): string
    {
        $resourceName = $this->toSnakeCase($name);
        $pluralName = $resourceName . 's';
        
        $prefix = $apiOnly ? '/api/' . $pluralName : '/' . $pluralName;
        
        return <<<PHP
<?php
declare(strict_types=1);

namespace App\Presentation\Controller;

use App\Infrastructure\Http\RequestInterface;
use App\Infrastructure\Http\Response;
use App\Infrastructure\Http\ResponseInterface;
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;
use App\Infrastructure\Validation\Attributes\Required;
use App\Infrastructure\Validation\Attributes\MinLength;
use App\Infrastructure\Validation\Attributes\MaxLength;
use App\Domain\Model\\{$name};

/**
 * {$name} Controller
 */
#[Controller(prefix: '{$prefix}')]
class {$name}Controller
{
    private const CONTENT_TYPE_JSON = 'application/json';

    #[Route(HttpMethod::GET, '/', name: '{$pluralName}.index')]
    public function index(): ResponseInterface
    {
        // TODO: Implement index method
        \${$pluralName} = [
            // Fetch from database
        ];

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode(['{$pluralName}' => \${$pluralName}]));
    }

    #[Route(HttpMethod::POST, '/', name: '{$pluralName}.store')]
    public function store(
        #[Required('Name is required')]
        #[MinLength(2, 'Name must be at least 2 characters')]
        #[MaxLength(255, 'Name must be at most 255 characters')]
        string \$name
    ): ResponseInterface {
        // TODO: Implement store method
        \${$resourceName} = new {$name}(\$name);
        
        // Save to database
        
        return (new Response())
            ->withStatus(201)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'message' => '{$name} created successfully',
                '{$resourceName}' => \${$resourceName}
            ]));
    }

    #[Route(HttpMethod::GET, '/{id}', name: '{$pluralName}.show')]
    public function show(string \$id): ResponseInterface
    {
        // TODO: Implement show method
        \${$resourceName} = null; // Fetch from database
        
        if (!\${$resourceName}) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode(['error' => '{$name} not found']));
        }

        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode(['{$resourceName}' => \${$resourceName}]));
    }

    #[Route(HttpMethod::PUT, '/{id}', name: '{$pluralName}.update')]
    public function update(
        string \$id,
        #[Required('Name is required')]
        #[MinLength(2, 'Name must be at least 2 characters')]
        #[MaxLength(255, 'Name must be at most 255 characters')]
        string \$name
    ): ResponseInterface {
        // TODO: Implement update method
        \${$resourceName} = null; // Fetch from database
        
        if (!\${$resourceName}) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode(['error' => '{$name} not found']));
        }
        
        // Update and save
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode([
                'message' => '{$name} updated successfully',
                '{$resourceName}' => \${$resourceName}
            ]));
    }

    #[Route(HttpMethod::DELETE, '/{id}', name: '{$pluralName}.destroy')]
    public function destroy(string \$id): ResponseInterface
    {
        // TODO: Implement destroy method
        \${$resourceName} = null; // Fetch from database
        
        if (!\${$resourceName}) {
            return (new Response())
                ->withStatus(404)
                ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
                ->write(json_encode(['error' => '{$name} not found']));
        }
        
        // Delete from database
        
        return (new Response())
            ->withStatus(200)
            ->withHeader('Content-Type', self::CONTENT_TYPE_JSON)
            ->write(json_encode(['message' => '{$name} deleted successfully']));
    }
}
PHP;
    }

    /**
     * Get migration template
     */
    private function getMigrationTemplate(string $className, string $tableName): string
    {
        return <<<PHP
<?php
declare(strict_types=1);

use App\Infrastructure\Persistence\Migration;

/**
 * {$className}
 */
class {$className} extends Migration
{
    public function up(): void
    {
        \$this->createTable('{$tableName}', [
            'id' => [
                'type' => 'INTEGER',
                'primary' => true,
                'autoincrement' => true
            ],
            'name' => [
                'type' => 'VARCHAR(255)',
                'null' => false
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => false
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => false
            ]
        ]);
    }

    public function down(): void
    {
        \$this->dropTable('{$tableName}');
    }
}
PHP;
    }

    /**
     * Get seeder template
     */
    private function getSeederTemplate(string $name): string
    {
        $tableName = $this->toSnakeCase($name) . 's';
        
        return <<<PHP
<?php
declare(strict_types=1);

use App\Infrastructure\Persistence\Seeder;

/**
 * {$name}Seeder
 */
class {$name}Seeder extends Seeder
{
    public function run(): void
    {
        \$this->insert('{$tableName}', [
            [
                'name' => 'Sample {$name} 1',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ],
            [
                'name' => 'Sample {$name} 2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]
        ]);
    }
}
PHP;
    }

    /**
     * Format class name
     */
    private function formatClassName(string $name): string
    {
        $name = str_replace(['-', '_'], ' ', $name);
        $name = ucwords($name);
        return str_replace(' ', '', $name);
    }

    /**
     * Convert to snake_case
     */
    private function toSnakeCase(string $string): string
    {
        return strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $string));
    }
}
