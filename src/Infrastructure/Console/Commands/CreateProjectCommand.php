<?php

declare(strict_types=1);

namespace App\Infrastructure\Console\Commands;

use App\Infrastructure\Console\Command;

class CreateProjectCommand extends Command
{
    private array $excludedPaths = [
        '.git',
        'node_modules',
        'vendor',
        'storage/cache',
        'storage/logs',
        'logs',
        'public/uploads',
        'storage/uploads',
        'storage/database/app.sqlite'
    ];

    protected function configure(): void
    {
        $this->setName('create:project');
        $this->setDescription('Create a new project based on MiniFramework PHP');
        $this->addArgument('name', true, 'The name of the project to create');
        $this->addOption('path', null, false, 'Custom path for the project');
        $this->addOption('namespace', null, false, 'Custom namespace for the project');
        $this->addOption('description', null, false, 'Project description');
    }

    protected function execute(array $arguments, array $options): int
    {
        if (!isset($arguments['name']) || empty($arguments['name'])) {
            $this->error('Project name is required');
            return 1;
        }

        $projectName = $arguments['name'];
        $targetPath = $options['path'] ?? getcwd() . DIRECTORY_SEPARATOR . $projectName;
        $namespace = $options['namespace'] ?? $this->generateNamespace($projectName);
        $description = $options['description'] ?? "A new project based on MiniFramework PHP";

        $this->info("Creating new project: {$projectName}");
        $this->info("Target path: {$targetPath}");
        $this->info("Namespace: {$namespace}");

        try {
            $this->createProjectStructure($projectName, $targetPath, $namespace, $description);
            $this->success("Project '{$projectName}' created successfully!");
            $this->info("Next steps:");
            $this->info("1. cd {$targetPath}");
            $this->info("2. composer install");
            $this->info("3. php bin/console key:generate");
            $this->info("4. php bin/console db:setup");
            $this->info("5. php bin/console serve");
        } catch (\Exception $e) {
            $this->error("Failed to create project: " . $e->getMessage());
            return 1;
        }

        return 0;
    }

    private function createProjectStructure(string $projectName, string $targetPath, string $namespace, string $description): void
    {
        $sourcePath = dirname(__DIR__, 4); // Go back to framework root

        // Create target directory
        if (!is_dir($targetPath)) {
            if (!mkdir($targetPath, 0755, true)) {
                throw new \RuntimeException("Failed to create directory: {$targetPath}");
            }
        }

        // Copy framework structure
        $this->copyDirectory($sourcePath, $targetPath);

        // Remove excluded files and directories
        $this->cleanupProject($targetPath);

        // Customize project files
        $this->customizeProject($targetPath, $projectName, $namespace, $description);

        // Initialize git repository
        $this->initializeGitRepository($targetPath, $projectName);
    }

    private function copyDirectory(string $source, string $target): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $targetPath = $target . DIRECTORY_SEPARATOR . $iterator->getSubPathName();

            // Skip excluded paths
            $relativePath = $iterator->getSubPathName();
            if ($this->shouldExcludePath($relativePath)) {
                continue;
            }

            if ($item->isDir()) {
                if (!is_dir($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }
            } else {
                $directory = dirname($targetPath);
                if (!is_dir($directory)) {
                    mkdir($directory, 0755, true);
                }
                copy($item->getPathname(), $targetPath);
            }
        }
    }

    private function shouldExcludePath(string $path): bool
    {
        foreach ($this->excludedPaths as $excludedPath) {
            if (strpos($path, $excludedPath) === 0) {
                return true;
            }
        }
        return false;
    }

    private function cleanupProject(string $targetPath): void
    {
        // Remove development-specific files
        $filesToRemove = [
            '.env',
            'storage/database/app.sqlite',
            'composer.lock'
        ];

        foreach ($filesToRemove as $file) {
            $filePath = $targetPath . DIRECTORY_SEPARATOR . $file;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // Create necessary directories
        $dirsToCreate = [
            'storage/cache/templates',
            'storage/logs',
            'storage/uploads',
            'storage/avatars',
            'public/uploads/avatars',
            'logs'
        ];

        foreach ($dirsToCreate as $dir) {
            $dirPath = $targetPath . DIRECTORY_SEPARATOR . $dir;
            if (!is_dir($dirPath)) {
                mkdir($dirPath, 0755, true);
            }
        }

        // Create .gitkeep files for empty directories
        $gitkeepDirs = [
            'storage/cache/templates',
            'storage/logs',
            'storage/uploads',
            'public/uploads/avatars',
            'logs'
        ];

        foreach ($gitkeepDirs as $dir) {
            $gitkeepPath = $targetPath . DIRECTORY_SEPARATOR . $dir . DIRECTORY_SEPARATOR . '.gitkeep';
            file_put_contents($gitkeepPath, '');
        }
    }

    private function customizeProject(string $targetPath, string $projectName, string $namespace, string $description): void
    {
        // Update composer.json
        $this->updateComposerJson($targetPath, $projectName, $namespace, $description);

        // Update README.md
        $this->updateReadme($targetPath, $projectName, $description);

        // Create environment file template
        $this->createEnvTemplate($targetPath);

        // Update namespace in source files
        $this->updateNamespaces($targetPath, $namespace);
    }

    private function updateComposerJson(string $targetPath, string $projectName, string $namespace, string $description): void
    {
        $composerPath = $targetPath . DIRECTORY_SEPARATOR . 'composer.json';
        $composer = json_decode(file_get_contents($composerPath), true);

        $composer['name'] = $this->generatePackageName($projectName);
        $composer['description'] = $description;
        $composer['autoload']['psr-4'] = [
            $namespace . '\\' => 'src/'
        ];
        $composer['autoload-dev']['psr-4'] = [
            'Tests\\' => 'tests/'
        ];

        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function updateReadme(string $targetPath, string $projectName, string $description): void
    {
        $readmePath = $targetPath . DIRECTORY_SEPARATOR . 'README.md';
        $readme = <<<MD
# {$projectName}

{$description}

## Installation

1. Clone this repository:
   ```bash
   git clone <repository-url> {$projectName}
   cd {$projectName}
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Set up environment:
   ```bash
   cp .env.example .env
   php bin/console key:generate
   ```

4. Initialize database:
   ```bash
   php bin/console db:setup
   php bin/console migrate
   ```

5. Start development server:
   ```bash
   php bin/console serve
   ```

## Features

This project is built on MiniFramework PHP, which includes:

- **Domain-Driven Design (DDD)** architecture
- **Clean Architecture** principles
- **PSR-4 Autoloading** with Composer
- **Dependency Injection Container**
- **Advanced Routing System**
- **Middleware Pipeline**
- **Rate Limiting**
- **CSRF Protection**
- **JWT Authentication**
- **File Upload System**
- **Template Engine**
- **Database Migrations**
- **CLI Commands**

## Development Commands

```bash
# Generate components
php bin/console make:controller UserController
php bin/console make:migration CreateUsersTable
php bin/console make:middleware AuthMiddleware

# Database operations
php bin/console migrate
php bin/console db:setup

# Development tools
php bin/console serve
php bin/console cache:clear
php bin/console test

# Security
php bin/console key:generate
php bin/console jwt:secret
```

## Project Structure

```
src/
├── Application/     # Application layer (Use Cases, Commands, Queries)
├── Domain/         # Domain layer (Entities, Value Objects, Services)
├── Infrastructure/ # Infrastructure layer (DB, HTTP, External services)
└── Presentation/   # Presentation layer (Controllers, Views)
```

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
MD;

        file_put_contents($readmePath, $readme);
    }

    private function createEnvTemplate(string $targetPath): void
    {
        $envExamplePath = $targetPath . DIRECTORY_SEPARATOR . '.env.example';
        $envContent = <<<ENV
# Application
APP_NAME="My Application"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=

# Database
DB_CONNECTION=sqlite
DB_DATABASE=storage/database/app.sqlite
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=

# JWT
JWT_SECRET=

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_WINDOW=60

# File Upload
MAX_FILE_SIZE=10M
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,txt,doc,docx

# CORS
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization
CORS_ALLOW_CREDENTIALS=false

# Security
CSRF_ENABLED=true
XSS_PROTECTION=true
CONTENT_TYPE_NOSNIFF=true
FRAME_OPTIONS=DENY
ENV;

        file_put_contents($envExamplePath, $envContent);
    }

    private function updateNamespaces(string $targetPath, string $namespace): void
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($targetPath . DIRECTORY_SEPARATOR . 'src'),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                $content = str_replace('namespace App\\', "namespace {$namespace}\\", $content);
                $content = str_replace('use App\\', "use {$namespace}\\", $content);
                file_put_contents($file->getPathname(), $content);
            }
        }
    }

    private function initializeGitRepository(string $targetPath, string $projectName): void
    {
        $gitignorePath = $targetPath . DIRECTORY_SEPARATOR . '.gitignore';
        $gitignoreContent = <<<GITIGNORE
# Dependencies
/vendor/
/node_modules/

# Environment
.env
.env.local
.env.*.local

# Cache and logs
/storage/cache/*
!/storage/cache/.gitkeep
/storage/logs/*
!/storage/logs/.gitkeep
/logs/*
!/logs/.gitkeep

# Database
/storage/database/*.sqlite
/storage/database/*.db

# Uploads
/storage/uploads/*
!/storage/uploads/.gitkeep
/public/uploads/*
!/public/uploads/.gitkeep

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Composer
composer.phar

# PHPUnit
.phpunit.result.cache
/coverage/
GITIGNORE;

        file_put_contents($gitignorePath, $gitignoreContent);

        // Initialize git repository if git is available
        $currentDir = getcwd();
        chdir($targetPath);
        
        if ($this->isCommandAvailable('git')) {
            exec('git init', $output, $returnCode);
            if ($returnCode === 0) {
                exec('git add .');
                exec("git commit -m \"Initial commit: {$projectName} project scaffolded\"");
                $this->info("Git repository initialized with initial commit");
            }
        }
        
        chdir($currentDir);
    }

    private function generateNamespace(string $projectName): string
    {
        // Convert project name to PascalCase for namespace
        $namespace = str_replace(['-', '_', ' '], '', ucwords($projectName, '-_ '));
        
        // Ensure it starts with a letter and contains only alphanumeric characters
        $namespace = preg_replace('/[^a-zA-Z0-9]/', '', $namespace);
        
        if (empty($namespace) || !ctype_alpha($namespace[0])) {
            $namespace = 'App';
        }
        
        return $namespace;
    }

    private function generatePackageName(string $projectName): string
    {
        // Convert to lowercase and replace spaces/underscores with hyphens
        $packageName = strtolower($projectName);
        $packageName = str_replace([' ', '_'], '-', $packageName);
        $packageName = preg_replace('/[^a-z0-9\-]/', '', $packageName);
        
        return "mycompany/{$packageName}";
    }

    private function isCommandAvailable(string $command): bool
    {
        $which = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        exec("{$which} {$command}", $output, $returnCode);
        return $returnCode === 0;
    }
}
