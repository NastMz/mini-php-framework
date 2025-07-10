#!/usr/bin/env pwsh

<#
.SYNOPSIS
    MiniFramework PHP Project Generator for Windows
.DESCRIPTION
    Creates new projects based on MiniFramework PHP with a simple interface
.PARAMETER ProjectName
    The name of the project to create
.PARAMETER Path
    Custom path for the project (default: current directory)
.PARAMETER Namespace
    Custom namespace (default: generated from project name)
.PARAMETER Description
    Project description
.PARAMETER NoGit
    Skip Git repository initialization
.PARAMETER NoInstall
    Skip dependency installation
.EXAMPLE
    .\create-miniframework-project.ps1 "my-api"
.EXAMPLE
    .\create-miniframework-project.ps1 "my-blog" -Path "C:\www\blog" -Namespace "Blog"
#>

param(
    [Parameter(Mandatory = $true, Position = 0)]
    [string]$ProjectName,
    
    [Parameter(Mandatory = $false)]
    [string]$Path = "",
    
    [Parameter(Mandatory = $false)]
    [string]$Namespace = "",
    
    [Parameter(Mandatory = $false)]
    [string]$Description = "A new project based on MiniFramework PHP",
    
    [Parameter(Mandatory = $false)]
    [switch]$NoGit,
    
    [Parameter(Mandatory = $false)]
    [switch]$NoInstall
)

# Configuration
$FrameworkRepo = "https://github.com/nastmz/mini-php-framework.git"
$TempDirPrefix = "miniframework_temp_"

function Show-Banner {
    Write-Host ""
    Write-Host "╔══════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
    Write-Host "║              MiniFramework PHP Project Generator             ║" -ForegroundColor Cyan
    Write-Host "║                     Create new projects                      ║" -ForegroundColor Cyan
    Write-Host "╚══════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
    Write-Host ""
}

function Write-Info {
    param([string]$Message)
    Write-Host "ℹ️  $Message" -ForegroundColor Blue
}

function Write-Success {
    param([string]$Message)
    Write-Host "✅ $Message" -ForegroundColor Green
}

function Write-Warning {
    param([string]$Message)
    Write-Host "⚠️  $Message" -ForegroundColor Yellow
}

function Write-Error {
    param([string]$Message)
    Write-Host "❌ $Message" -ForegroundColor Red
}

function Test-Command {
    param([string]$Command)
    try {
        Get-Command $Command -ErrorAction Stop | Out-Null
        return $true
    }
    catch {
        return $false
    }
}

function Generate-Namespace {
    param([string]$ProjectName)
    
    $namespace = ($ProjectName -replace '[^a-zA-Z0-9]', '') -replace '^[^a-zA-Z]', ''
    if ([string]::IsNullOrEmpty($namespace)) {
        return "App"
    }
    
    # Convert to PascalCase
    $namespace = (Get-Culture).TextInfo.ToTitleCase($namespace.ToLower())
    return $namespace
}

function Generate-PackageName {
    param([string]$ProjectName)
    
    $packageName = $ProjectName.ToLower() -replace '[^a-z0-9\-]', '-' -replace '-+', '-'
    return "mycompany/$packageName"
}

function New-ProjectStructure {
    param(
        [string]$SourcePath,
        [string]$TargetPath,
        [array]$ExcludedPaths
    )
    
    Write-Info "Copying framework structure..."
    
    # Create target directory if it doesn't exist
    if (!(Test-Path $TargetPath)) {
        New-Item -ItemType Directory -Path $TargetPath -Force | Out-Null
    }
    
    # Copy files and directories
    Get-ChildItem -Path $SourcePath -Recurse | ForEach-Object {
        $relativePath = $_.FullName.Substring($SourcePath.Length + 1)
        $shouldExclude = $false
        
        foreach ($excludePath in $ExcludedPaths) {
            if ($relativePath -like $excludePath) {
                $shouldExclude = $true
                break
            }
        }
        
        if (-not $shouldExclude) {
            $targetItemPath = Join-Path $TargetPath $relativePath
            
            if ($_.PSIsContainer) {
                if (!(Test-Path $targetItemPath)) {
                    New-Item -ItemType Directory -Path $targetItemPath -Force | Out-Null
                }
            } else {
                $targetDir = Split-Path $targetItemPath -Parent
                if (!(Test-Path $targetDir)) {
                    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null
                }
                Copy-Item $_.FullName $targetItemPath -Force
            }
        }
    }
}

function New-ProjectDirectories {
    param([string]$TargetPath)
    
    $dirsToCreate = @(
        "storage\cache\templates",
        "storage\logs",
        "storage\uploads",
        "storage\avatars",
        "public\uploads\avatars",
        "logs"
    )
    
    foreach ($dir in $dirsToCreate) {
        $dirPath = Join-Path $TargetPath $dir
        if (!(Test-Path $dirPath)) {
            New-Item -ItemType Directory -Path $dirPath -Force | Out-Null
        }
        
        # Create .gitkeep file
        $gitkeepPath = Join-Path $dirPath ".gitkeep"
        if (!(Test-Path $gitkeepPath)) {
            "" | Out-File -FilePath $gitkeepPath -Encoding utf8
        }
    }
}

function Update-ComposerJson {
    param(
        [string]$ProjectPath,
        [string]$ProjectName,
        [string]$Namespace,
        [string]$Description
    )
    
    $composerPath = Join-Path $ProjectPath "composer.json"
    $composer = Get-Content $composerPath | ConvertFrom-Json
    
    $composer.name = Generate-PackageName $ProjectName
    $composer.description = $Description
    $composer.autoload.'psr-4' = @{ "$Namespace\" = "src/" }
    
    $composer | ConvertTo-Json -Depth 10 | Set-Content $composerPath -Encoding utf8
}

function Update-Namespaces {
    param(
        [string]$ProjectPath,
        [string]$Namespace
    )
    
    $srcPath = Join-Path $ProjectPath "src"
    if (Test-Path $srcPath) {
        Get-ChildItem -Path $srcPath -Recurse -Filter "*.php" | ForEach-Object {
            $content = Get-Content $_.FullName -Raw
            $content = $content -replace "namespace App\\", "namespace $Namespace\"
            $content = $content -replace "use App\\", "use $Namespace\"
            Set-Content $_.FullName $content -Encoding utf8
        }
    }
}

function New-EnvTemplate {
    param([string]$ProjectPath)
    
    $envPath = Join-Path $ProjectPath ".env.example"
    $envContent = @"
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
"@

    Set-Content $envPath $envContent -Encoding utf8
}

function New-ReadmeFile {
    param(
        [string]$ProjectPath,
        [string]$ProjectName,
        [string]$Description
    )
    
    $readmePath = Join-Path $ProjectPath "README.md"
    $readmeContent = @"
# $ProjectName

$Description

Built with [MiniFramework PHP](https://github.com/nastmz/mini-php-framework) - A modern PHP micro-framework with DDD and Clean Architecture.

## Quick Start

1. **Install dependencies:**
   ``````powershell
   composer install
   ``````

2. **Set up environment:**
   ``````powershell
   Copy-Item .env.example .env
   php bin/console key:generate
   ``````

3. **Initialize database:**
   ``````powershell
   php bin/console db:setup
   php bin/console migrate
   ``````

4. **Start development server:**
   ``````powershell
   php bin/console serve
   ``````

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

``````powershell
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
``````

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).
"@

    Set-Content $readmePath $readmeContent -Encoding utf8
}

function Initialize-GitRepository {
    param(
        [string]$ProjectPath,
        [string]$ProjectName
    )
    
    if ($NoGit) {
        return
    }
    
    Write-Info "Initializing Git repository..."
    
    # Create .gitignore
    $gitignorePath = Join-Path $ProjectPath ".gitignore"
    $gitignoreContent = @"
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
"@

    Set-Content $gitignorePath $gitignoreContent -Encoding utf8
    
    # Initialize git repository
    if (Test-Command "git") {
        Push-Location $ProjectPath
        try {
            git init | Out-Null
            git add . | Out-Null
            git commit -m "🎉 Initial commit: $ProjectName project created with MiniFramework PHP" | Out-Null
            Write-Success "Git repository initialized with initial commit"
        }
        catch {
            Write-Warning "Failed to initialize Git repository"
        }
        finally {
            Pop-Location
        }
    } else {
        Write-Warning "Git not found. Please install Git to initialize repository."
    }
}

function Install-Dependencies {
    param([string]$ProjectPath)
    
    if ($NoInstall) {
        return
    }
    
    if (!(Test-Command "composer")) {
        Write-Warning "Composer not found. Please install dependencies manually with: composer install"
        return
    }
    
    Write-Info "Installing dependencies..."
    
    Push-Location $ProjectPath
    try {
        composer install --no-dev --optimize-autoloader | Out-Null
        Write-Success "Dependencies installed successfully"
    }
    catch {
        Write-Warning "Failed to install dependencies. Please run 'composer install' manually."
    }
    finally {
        Pop-Location
    }
}

function Show-CompletionMessage {
    param(
        [string]$ProjectName,
        [string]$ProjectPath
    )
    
    Write-Host ""
    Write-Host "🎉 Project '$ProjectName' created successfully!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📁 Location: $ProjectPath" -ForegroundColor Cyan
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Yellow
    Write-Host "  1️⃣  cd $(Split-Path $ProjectPath -Leaf)" -ForegroundColor White
    if ($NoInstall) {
        Write-Host "  2️⃣  composer install" -ForegroundColor White
    }
    Write-Host "  3️⃣  Copy-Item .env.example .env" -ForegroundColor White
    Write-Host "  4️⃣  php bin/console key:generate" -ForegroundColor White
    Write-Host "  5️⃣  php bin/console db:setup" -ForegroundColor White
    Write-Host "  6️⃣  php bin/console serve" -ForegroundColor White
    Write-Host ""
    Write-Host "🚀 Visit http://localhost:8000 to see your application!" -ForegroundColor Green
    Write-Host ""
    Write-Host "📚 Documentation: https://github.com/nastmz/mini-php-framework" -ForegroundColor Cyan
    Write-Host ""
}

# Main execution
try {
    Show-Banner
    
    # Set default values
    if ([string]::IsNullOrEmpty($Path)) {
        $Path = Join-Path (Get-Location) $ProjectName
    }
    
    if ([string]::IsNullOrEmpty($Namespace)) {
        $Namespace = Generate-Namespace $ProjectName
    }
    
    Write-Info "Creating new project: $ProjectName"
    Write-Info "Target path: $Path"
    Write-Info "Namespace: $Namespace"
    
    # Check if target directory exists and is not empty
    if (Test-Path $Path) {
        $items = Get-ChildItem $Path
        if ($items.Count -gt 0) {
            throw "Target directory already exists and is not empty: $Path"
        }
    }
    
    # Check if Git is available
    if (!(Test-Command "git")) {
        throw "Git is required to download the framework. Please install Git and try again."
    }
    
    # Create temporary directory
    $tempDir = Join-Path $env:TEMP ($TempDirPrefix + [System.Guid]::NewGuid().ToString())
    
    try {
        # Clone framework
        Write-Info "Downloading MiniFramework PHP..."
        git clone --depth 1 $FrameworkRepo $tempDir | Out-Null
        
        # Define excluded paths
        $excludedPaths = @(
            ".git*",
            "node_modules*",
            "vendor*",
            "storage\cache\templates\*",
            "storage\logs\*",
            "logs\*",
            "public\uploads\*",
            "storage\uploads\*",
            "storage\database\app.sqlite",
            "composer.lock",
            ".env"
        )
        
        # Copy framework structure
        New-ProjectStructure -SourcePath $tempDir -TargetPath $Path -ExcludedPaths $excludedPaths
        
        # Create necessary directories
        New-ProjectDirectories -TargetPath $Path
        
        # Customize project
        Write-Info "Customizing project..."
        Update-ComposerJson -ProjectPath $Path -ProjectName $ProjectName -Namespace $Namespace -Description $Description
        New-ReadmeFile -ProjectPath $Path -ProjectName $ProjectName -Description $Description
        New-EnvTemplate -ProjectPath $Path
        Update-Namespaces -ProjectPath $Path -Namespace $Namespace
        
        # Initialize Git repository
        Initialize-GitRepository -ProjectPath $Path -ProjectName $ProjectName
        
        # Install dependencies
        Install-Dependencies -ProjectPath $Path
        
        # Show completion message
        Show-CompletionMessage -ProjectName $ProjectName -ProjectPath $Path
        
    }
    finally {
        # Cleanup temporary directory
        if (Test-Path $tempDir) {
            Remove-Item $tempDir -Recurse -Force
        }
    }
}
catch {
    Write-Error "Failed to create project: $($_.Exception.Message)"
    exit 1
}
