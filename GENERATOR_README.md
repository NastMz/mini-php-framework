# MiniFramework PHP - Project Generator

This tool allows you to create new projects based on MiniFramework PHP quickly and easily.

## 🚀 Usage Methods

### 1. 🌍 Global Installer (Recommended - Laravel Style)

The easiest way to create projects is using our global installer:

```bash
# Install the global installer once
composer global require miniframework/installer

# Create projects from anywhere
miniframework new my-project
miniframework new my-api --namespace=MyApi --path=/var/www/api
miniframework new blog --description="My personal blog"
```

**Quick Installation:**

```bash
# Unix/Linux/macOS
curl -sSL https://raw.githubusercontent.com/miniframework/installer/main/install.sh | bash

# Windows (PowerShell)
Invoke-WebRequest -Uri "https://raw.githubusercontent.com/miniframework/installer/main/install.bat" -OutFile "install.bat" && .\install.bat
```

### 2. Integrated CLI Command (Inside Framework)

From within the framework directory, you can use the integrated CLI command:

```bash
# Create a basic project
php bin/console create:project my-new-project

# Create a project with custom options
php bin/console create:project my-api --path=/var/www/my-api --namespace=MyApi --description="My REST API"
```

**Available options:**

- `--path`: Custom path for the project (default: current directory + project name)
- `--namespace`: Custom namespace (default: auto-generated from name)
- `--description`: Project description

### 3. Standalone PHP Script

For independent use outside the framework:

```bash
# Use the PHP script directly
php create-miniframework-project.php my-new-project

# With custom options
php create-miniframework-project.php my-blog --path=/var/www/blog --namespace=Blog --description="My personal blog"

# Without Git initialization or dependency installation
php create-miniframework-project.php my-project --no-git --no-install
```

**Available options:**

- `--path=PATH`: Custom path for the project
- `--namespace=NAMESPACE`: Custom namespace
- `--description=DESC`: Project description
- `--no-git`: Skip Git repository initialization
- `--no-install`: Skip dependency installation

### 4. PowerShell Script (Windows)

For Windows users with PowerShell:

```powershell
# Create a basic project
.\create-miniframework-project.ps1 "my-new-project"

# With custom options
.\create-miniframework-project.ps1 "my-blog" -Path "C:\www\blog" -Namespace "Blog" -Description "My personal blog"

# Without Git or dependency installation
.\create-miniframework-project.ps1 "my-project" -NoGit -NoInstall
```

**Available parameters:**

- `-Path`: Custom path for the project
- `-Namespace`: Custom namespace
- `-Description`: Project description
- `-NoGit`: Skip Git repository initialization
- `-NoInstall`: Skip dependency installation

### 5. Batch Script (Windows)

For Windows users with Command Prompt:

```cmd
# Create a basic project
create-miniframework-project.bat my-new-project

# Note: Batch script uses default settings only
```

---

## 🛠️ Generator Features

### ✅ What it does automatically

1. **Copies the complete framework structure**
2. **Excludes unnecessary files** (vendor/, .git/, logs/, etc.)
3. **Creates necessary directories** with .gitkeep files
4. **Updates composer.json** with the new name and namespace
5. **Updates all namespaces** in the source code
6. **Generates a custom README.md** for the project
7. **Creates a .env.example file** with all configurations
8. **Initializes a Git repository** with appropriate .gitignore
9. **Installs Composer dependencies** automatically
10. **Makes an initial commit** in Git

### 📁 Generated Structure

```text
my-new-project/
├── bin/                    # CLI console and scripts
├── bootstrap/              # Bootstrap files
├── config/                 # Configuration files
├── migrations/             # Database migrations
├── public/                 # Web-accessible files
│   ├── index.php
│   ├── assets/
│   └── uploads/
├── seeders/                # Database seeders
├── src/                    # Application source code
│   ├── Application/        # Application layer (Use Cases)
│   ├── Domain/            # Domain layer (Entities)
│   ├── Infrastructure/    # Infrastructure layer
│   └── Presentation/      # Presentation layer (Controllers)
├── storage/               # Storage and cache
│   ├── cache/
│   ├── database/
│   ├── logs/
│   └── uploads/
├── tests/                 # Unit and integration tests
├── views/                 # View templates
├── composer.json          # Composer configuration
├── .env.example          # Environment variables template
├── .gitignore           # Git configuration
└── README.md            # Project documentation
```

---

## 📋 Requirements

- **PHP 8.4+**
- **Composer**
- **Git** (to download the framework and initialize repository)

## 🎯 Steps after creating a project

1. **Navigate to the project directory:**

   ```bash
   cd my-new-project
   ```

2. **Configure the environment:**

   ```bash
   cp .env.example .env
   php bin/console key:generate
   ```

3. **Initialize the database:**

   ```bash
   php bin/console db:setup
   php bin/console migrate
   ```

4. **Start the development server:**

   ```bash
   php bin/console serve
   ```

5. **Visit your application:**
   Open <http://localhost:8000> in your browser

---

## 🔧 Advanced Customization

### Modify excluded files

If you need to customize which files are excluded during copying, you can modify the `$excludedPaths` variable in the scripts:

```php
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
```

### Customize templates

Automatically generated files (README.md, .env.example, .gitignore) can be customized by modifying the corresponding methods in the scripts.

---

## 🚀 Usage Examples

### REST API

```bash
php create-miniframework-project.php my-rest-api \
  --namespace=RestApi \
  --description="REST API for my application"
```

### Complete web application

```bash
php create-miniframework-project.php my-webapp \
  --path=/var/www/webapp \
  --namespace=WebApp \
  --description="Complete web application with authentication"
```

### Microservice

```bash
php create-miniframework-project.php auth-service \
  --namespace=AuthService \
  --description="Authentication microservice"
```

---

## 📚 Additional Documentation

- [Framework Documentation](../README.md)
- [CLI Commands](../CLI_HELP.md)
- [Usage Examples](../USAGE_EXAMPLES.md)
- [GitHub Repository](https://github.com/nastmz/mini-php-framework)

---

## 🤝 Contributing

If you find any issues or have suggestions to improve the project generator:

1. Open an issue on GitHub
2. Submit a pull request with improvements
3. Share feedback about the user experience

---

## 📝 License

This project generator is included under the same MIT license as the main framework.

---

**MiniFramework PHP Project Generator** - Create new projects quickly and efficiently.
