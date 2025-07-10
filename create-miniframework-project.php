#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * MiniFramework PHP Project Generator
 * 
 * This script creates new projects based on MiniFramework PHP.
 * Usage: php create-miniframework-project.php <project-name> [options]
 */

class MiniFrameworkProjectGenerator
{
    private const FRAMEWORK_REPO = 'https://github.com/nastmz/mini-php-framework.git';
    private const TEMP_DIR_PREFIX = 'miniframework_temp_';
    
    private array $excludedPaths = [
        '.git',
        'node_modules',
        'vendor',
        'storage/cache/templates/*',
        'storage/logs/*',
        'logs/*',
        'public/uploads/*',
        'storage/uploads/*',
        'storage/database/app.sqlite',
        'composer.lock',
        '.env'
    ];

    public function __construct(
        private array $argv = []
    ) {}

    public function run(): int
    {
        $this->showBanner();

        if (!$this->validateArguments()) {
            $this->showUsage();
            return 1;
        }

        $options = $this->parseArguments();

        try {
            $this->createProject($options);
            $this->showSuccess($options);
            return 0;
        } catch (\Exception $e) {
            $this->showError("Failed to create project: " . $e->getMessage());
            return 1;
        }
    }

    private function showBanner(): void
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║              MiniFramework PHP Project Generator             ║\n";
        echo "║                     Create new projects                      ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
    }

    private function validateArguments(): bool
    {
        return count($this->argv) >= 2;
    }

    private function parseArguments(): array
    {
        $projectName = $this->argv[1];
        $options = [
            'name' => $projectName,
            'path' => getcwd() . DIRECTORY_SEPARATOR . $projectName,
            'namespace' => $this->generateNamespace($projectName),
            'description' => "A new project based on MiniFramework PHP",
            'git' => true,
            'install' => true
        ];

        // Parse additional options
        for ($i = 2; $i < count($this->argv); $i++) {
            $arg = $this->argv[$i];
            
            if (str_starts_with($arg, '--path=')) {
                $options['path'] = substr($arg, 7);
            } elseif (str_starts_with($arg, '--namespace=')) {
                $options['namespace'] = substr($arg, 12);
            } elseif (str_starts_with($arg, '--description=')) {
                $options['description'] = substr($arg, 14);
            } elseif ($arg === '--no-git') {
                $options['git'] = false;
            } elseif ($arg === '--no-install') {
                $options['install'] = false;
            }
        }

        return $options;
    }

    private function createProject(array $options): void
    {
        $this->info("Creating new project: {$options['name']}");
        $this->info("Target path: {$options['path']}");
        $this->info("Namespace: {$options['namespace']}");

        // Create temporary directory for cloning
        $tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . self::TEMP_DIR_PREFIX . uniqid();
        
        try {
            // Clone or copy framework
            $this->downloadFramework($tempDir);
            
            // Create target directory
            $this->createTargetDirectory($options['path']);
            
            // Copy framework structure
            $this->copyFrameworkStructure($tempDir, $options['path']);
            
            // Customize project
            $this->customizeProject($options);
            
            // Initialize git repository
            if ($options['git']) {
                $this->initializeGitRepository($options['path'], $options['name']);
            }
            
            // Install dependencies
            if ($options['install']) {
                $this->installDependencies($options['path']);
            }
            
        } finally {
            // Cleanup temporary directory
            if (is_dir($tempDir)) {
                $this->removeDirectory($tempDir);
            }
        }
    }

    private function downloadFramework(string $tempDir): void
    {
        $this->info("Setting up framework source...");
        
        // Check if we're running from within the framework directory
        $currentDir = __DIR__;
        if (file_exists($currentDir . '/composer.json') && file_exists($currentDir . '/src')) {
            $this->info("Using local framework source...");
            $this->copyLocalFramework($currentDir, $tempDir);
            return;
        }
        
        if ($this->isCommandAvailable('git')) {
            // Clone from git repository
            $command = "git clone --depth 1 " . self::FRAMEWORK_REPO . " " . escapeshellarg($tempDir);
            exec($command, $output, $returnCode);
            
            if ($returnCode !== 0) {
                throw new \RuntimeException("Failed to clone framework repository. Please check the repository URL or run this script from within the framework directory.");
            }
        } else {
            throw new \RuntimeException("Git is required to download the framework, or run this script from within the framework directory.");
        }
    }

    private function copyLocalFramework(string $source, string $target): void
    {
        if (!is_dir($target)) {
            mkdir($target, 0755, true);
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $iterator->getSubPathName();
            $targetPath = $target . DIRECTORY_SEPARATOR . $relativePath;

            // Skip excluded paths
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

    private function createTargetDirectory(string $path): void
    {
        if (is_dir($path) && count(scandir($path)) > 2) {
            throw new \RuntimeException("Target directory already exists and is not empty: {$path}");
        }

        if (!is_dir($path)) {
            if (!mkdir($path, 0755, true)) {
                throw new \RuntimeException("Failed to create directory: {$path}");
            }
        }
    }

    private function copyFrameworkStructure(string $source, string $target): void
    {
        $this->info("Copying framework structure...");
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $item) {
            $relativePath = $iterator->getSubPathName();
            $targetPath = $target . DIRECTORY_SEPARATOR . $relativePath;

            // Skip excluded paths
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

        // Create necessary directories and .gitkeep files
        $this->createProjectDirectories($target);
    }

    private function shouldExcludePath(string $path): bool
    {
        foreach ($this->excludedPaths as $excludedPath) {
            if (fnmatch($excludedPath, $path) || str_starts_with($path, rtrim($excludedPath, '/*'))) {
                return true;
            }
        }
        return false;
    }

    private function createProjectDirectories(string $targetPath): void
    {
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
            
            // Create .gitkeep file
            $gitkeepPath = $dirPath . DIRECTORY_SEPARATOR . '.gitkeep';
            if (!file_exists($gitkeepPath)) {
                file_put_contents($gitkeepPath, '');
            }
        }
    }

    private function customizeProject(array $options): void
    {
        $this->info("Customizing project...");
        
        // Update composer.json
        $this->updateComposerJson($options);
        
        // Update README.md
        $this->updateReadme($options);
        
        // Create environment file template
        $this->createEnvTemplate($options['path']);
        
        // Update namespace in source files
        $this->updateNamespaces($options['path'], $options['namespace']);
    }

    private function updateComposerJson(array $options): void
    {
        $composerPath = $options['path'] . DIRECTORY_SEPARATOR . 'composer.json';
        $composer = json_decode(file_get_contents($composerPath), true);

        $composer['name'] = $this->generatePackageName($options['name']);
        $composer['description'] = $options['description'];
        $composer['autoload']['psr-4'] = [
            $options['namespace'] . '\\' => 'src/'
        ];

        file_put_contents($composerPath, json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    private function updateReadme(array $options): void
    {
        $readmePath = $options['path'] . DIRECTORY_SEPARATOR . 'README.md';
        $readme = <<<MD
# {$options['name']}

{$options['description']}

Built with [MiniFramework PHP](https://github.com/nastmz/mini-php-framework) - A modern PHP micro-framework with DDD and Clean Architecture.

## Quick Start

1. **Install dependencies:**
   ```bash
   composer install
   ```

2. **Set up environment:**
   ```bash
   cp .env.example .env
   php bin/console key:generate
   ```

3. **Initialize database:**
   ```bash
   php bin/console db:setup
   php bin/console migrate
   ```

4. **Start development server:**
   ```bash
   php bin/console serve
   ```

Visit http://localhost:8000 to see your application running!

## Framework Features

- ✅ **Domain-Driven Design (DDD)** architecture
- ✅ **Clean Architecture** principles  
- ✅ **Dependency Injection** container with autowiring
- ✅ **Advanced Routing** with attributes and parameters
- ✅ **Middleware Pipeline** (PSR-15 compatible)
- ✅ **Rate Limiting** with multiple backends
- ✅ **CSRF Protection** for forms and AJAX
- ✅ **JWT Authentication** with refresh tokens
- ✅ **File Upload System** with validation
- ✅ **Template Engine** with layouts and components
- ✅ **Database Migrations** and seeders
- ✅ **CLI Commands** for development
- ✅ **Error Handling** with custom pages
- ✅ **Security Headers** and CORS support

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
php bin/console routes:list

# Security
php bin/console key:generate
php bin/console jwt:secret
```

## Project Structure

```
{$options['name']}/
├── bin/                 # CLI console and scripts
├── bootstrap/           # Application bootstrap files
├── config/             # Configuration files
├── migrations/         # Database migrations
├── public/             # Web accessible files
├── seeders/           # Database seeders
├── src/               # Application source code
│   ├── Application/   # Use Cases, Commands, Queries
│   ├── Domain/        # Entities, Value Objects, Services
│   ├── Infrastructure/# External concerns (DB, HTTP, etc.)
│   └── Presentation/  # Controllers, Views
├── storage/           # File storage and cache
├── tests/             # Test suites
└── views/             # Template files
```

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
MD;

        file_put_contents($readmePath, $readme);
    }

    private function createEnvTemplate(string $targetPath): void
    {
        $envExamplePath = $targetPath . DIRECTORY_SEPARATOR . '.env.example';
        $envContent = <<<ENV
# Application Configuration
APP_NAME="My Application"
APP_ENV=development
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=

# Database Configuration
DB_CONNECTION=sqlite
DB_DATABASE=storage/database/app.sqlite
DB_HOST=localhost
DB_PORT=3306
DB_USERNAME=root
DB_PASSWORD=

# JWT Configuration
JWT_SECRET=
JWT_ALGORITHM=HS256
JWT_EXPIRATION=3600
JWT_REFRESH_EXPIRATION=604800

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_ATTEMPTS=60
RATE_LIMIT_WINDOW=60
RATE_LIMIT_STORAGE=file

# File Upload Configuration
MAX_FILE_SIZE=10M
ALLOWED_EXTENSIONS=jpg,jpeg,png,gif,pdf,txt,doc,docx
UPLOAD_PATH=storage/uploads

# CORS Configuration
CORS_ENABLED=true
CORS_ALLOWED_ORIGINS=*
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With
CORS_ALLOW_CREDENTIALS=false

# Security Configuration
CSRF_ENABLED=true
XSS_PROTECTION=true
CONTENT_TYPE_NOSNIFF=true
FRAME_OPTIONS=DENY

# Logging Configuration
LOG_LEVEL=info
LOG_CHANNEL=file
ENV;

        file_put_contents($envExamplePath, $envContent);
    }

    private function updateNamespaces(string $targetPath, string $namespace): void
    {
        $srcPath = $targetPath . DIRECTORY_SEPARATOR . 'src';
        
        if (!is_dir($srcPath)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($srcPath),
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
        $this->info("Initializing Git repository...");
        
        // Create .gitignore
        $gitignorePath = $targetPath . DIRECTORY_SEPARATOR . '.gitignore';
        $gitignoreContent = <<<GITIGNORE
# Dependencies
/vendor/
/node_modules/

# Environment files
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

# IDE files
.vscode/
.idea/
*.swp
*.swo
*~

# OS files
.DS_Store
.DS_Store?
._*
.Spotlight-V100
.Trashes
ehthumbs.db
Thumbs.db

# Composer
composer.phar
composer.lock

# PHPUnit
.phpunit.result.cache
/coverage/
/build/

# Temporary files
*.tmp
*.temp
GITIGNORE;

        file_put_contents($gitignorePath, $gitignoreContent);

        // Initialize git repository
        $currentDir = getcwd();
        chdir($targetPath);
        
        if ($this->isCommandAvailable('git')) {
            exec('git init', $output, $returnCode);
            if ($returnCode === 0) {
                exec('git add .');
                exec("git commit -m \"🎉 Initial commit: {$projectName} project created with MiniFramework PHP\"");
                $this->info("Git repository initialized with initial commit");
            }
        }
        
        chdir($currentDir);
    }

    private function installDependencies(string $targetPath): void
    {
        if (!$this->isCommandAvailable('composer')) {
            $this->warning("Composer not found. Please install dependencies manually with: composer install");
            return;
        }

        $this->info("Installing dependencies...");
        
        $currentDir = getcwd();
        chdir($targetPath);
        
        exec('composer install --no-dev --optimize-autoloader', $output, $returnCode);
        
        chdir($currentDir);
        
        if ($returnCode === 0) {
            $this->info("Dependencies installed successfully");
        } else {
            $this->warning("Failed to install dependencies. Please run 'composer install' manually.");
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }

        rmdir($dir);
    }

    private function generateNamespace(string $projectName): string
    {
        $namespace = str_replace(['-', '_', ' '], '', ucwords($projectName, '-_ '));
        $namespace = preg_replace('/[^a-zA-Z0-9]/', '', $namespace);
        
        if (empty($namespace) || !ctype_alpha($namespace[0])) {
            $namespace = 'App';
        }
        
        return $namespace;
    }

    private function generatePackageName(string $projectName): string
    {
        $packageName = strtolower($projectName);
        $packageName = str_replace([' ', '_'], '-', $packageName);
        $packageName = preg_replace('/[^a-z0-9\-]/', '', $packageName);
        
        return "mycompany/{$packageName}";
    }

    private function isCommandAvailable(string $command): bool
    {
        $which = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        exec("{$which} {$command} 2>nul", $output, $returnCode);
        return $returnCode === 0;
    }

    private function showUsage(): void
    {
        echo "Usage: php create-miniframework-project.php <project-name> [options]\n\n";
        echo "Arguments:\n";
        echo "  project-name              The name of the project to create\n\n";
        echo "Options:\n";
        echo "  --path=PATH              Custom path for the project (default: ./project-name)\n";
        echo "  --namespace=NAMESPACE    Custom namespace (default: generated from project name)\n";
        echo "  --description=DESC       Project description\n";
        echo "  --no-git                 Skip Git repository initialization\n";
        echo "  --no-install             Skip dependency installation\n\n";
        echo "Examples:\n";
        echo "  php create-miniframework-project.php my-api\n";
        echo "  php create-miniframework-project.php my-blog --path=/var/www/blog\n";
        echo "  php create-miniframework-project.php ecommerce --namespace=Store\n";
        echo "\n";
    }

    private function showSuccess(array $options): void
    {
        echo "\n";
        echo "🎉 Project '{$options['name']}' created successfully!\n\n";
        echo "📁 Location: {$options['path']}\n";
        echo "📦 Namespace: {$options['namespace']}\n\n";
        echo "Next steps:\n";
        echo "  1️⃣  cd " . basename($options['path']) . "\n";
        if (!$options['install']) {
            echo "  2️⃣  composer install\n";
        }
        echo "  3️⃣  cp .env.example .env\n";
        echo "  4️⃣  php bin/console key:generate\n";
        echo "  5️⃣  php bin/console db:setup\n";
        echo "  6️⃣  php bin/console serve\n\n";
        echo "🚀 Visit http://localhost:8000 to see your application!\n\n";
        echo "📚 Documentation: https://github.com/nastmz/mini-php-framework\n";
        echo "\n";
    }

    private function info(string $message): void
    {
        echo "ℹ️  {$message}\n";
    }

    private function warning(string $message): void
    {
        echo "⚠️  {$message}\n";
    }

    private function showError(string $message): void
    {
        echo "❌ {$message}\n";
    }
}

// Run the generator
if (basename(__FILE__) === basename($_SERVER['SCRIPT_NAME'])) {
    $generator = new MiniFrameworkProjectGenerator($argv);
    exit($generator->run());
}
