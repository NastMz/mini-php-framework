# MiniFramework PHP

A personal learning project - a PHP micro-framework built from scratch to explore Domain-Driven Design (DDD) and Clean Architecture princ## 🚀 Create New Projects

MiniFramework PHP offers multiple ways to create new projects quickly:

### 🌍 Global Installer (Recommended - Laravel Style)

Install the global installer once and create projects from anywhere:

```bash
# Install globally via Composer
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

### Using CLI Command (from framework directory). Created as an academic exercise to understand framework internals and modern PHP 8.4+ features.

> **⚠️ Educational Project Notice**: This framework was developed for learning purposes and academic curiosity. It is not intended for production use. The goal was to understand how modern frameworks work under the hood and to practice implementing design patterns like DDD and Clean Architecture.

---

## 🚀 Features

### Core Framework

- **PSR-4 Autoloading** with Composer
- **Modular Architecture** with clean bootstrap system
- **Dependency Injection Container** with auto-registration and autowiring
- **Advanced Routing System** with HTTP method enums, parameterized routes, and attribute-based routing
- **PSR-15 Compatible Middleware Pipeline** with conditional middleware stacks

### HTTP Layer

- **Custom Request/Response Interfaces** with full HTTP support
- **Global Error Handler** with intelligent exception mapping
- **CORS Support** with configurable origins, methods, and headers
- **Security Headers** middleware (CSP, XSS protection, frame options)
- **Rate Limiting** with database-backed or file-based storage
- **CSRF Protection** with token validation for forms and AJAX

### Database & ORM

- **Lightweight ORM** with PHP 8 attributes (`#[Table]`, `#[Column]`)
- **Fluent Query Builder** (SELECT, INSERT, UPDATE, DELETE, WHERE, JOIN)
- **Entity Manager** with automatic mapping and persistence
- **ACID Transactions** with helper methods
- **Migration System** with up/down methods and version control
- **Database Seeders** for test data population
- **SQLite Support** with auto-creation of database files

### Validation & Serialization

- **Automatic Validation** using PHP 8 attributes (`#[Required]`, `#[Email]`, `#[MinLength]`, `#[MaxLength]`, `#[Numeric]`, `#[In]`)
- **Auto-Validation Middleware** returning 422 responses with field-level errors
- **Auto-Serialization** with attribute-based configuration
- **JSON API Support** with automatic content negotiation

### Security

- **Complete JWT Authentication System** with login/logout endpoints and user management
- **JWT Authentication Middleware** for protecting routes
- **Application Key Management** with encryption/decryption using AES-256-CBC
- **Password Hashing** with PHP's `password_hash()` (bcrypt)
- **CSRF Token Management** with session-based validation
- **Security Headers** middleware (CSP, XSS protection, frame options)
- **Request ID Tracking** for audit trails

### Command Query Responsibility Segregation (CQRS)

- **Command Bus** with handler mapping and auto-registration
- **Query Bus** with automatic handler resolution
- **Domain Events** with subscriber pattern
- **Event Dispatcher** with logging subscriber

### Templating & Views

- **Custom Template Engine** with layouts, sections, and inheritance
- **Template Caching** for improved performance
- **Auto-escaping** for XSS prevention
- **Section Management** (`@extends`, `@section`, `@yield`)
- **CSP-Compliant Assets** with external CSS/JS file loading

### File Management

- **File Storage Interface** with local storage implementation
- **File Upload Service** with size/type validation
- **Automatic Upload Handling** with configurable storage paths
- **File Validation** with MIME type checking

### Monitoring & Health

- **Health Check Endpoint** (`/healthz`) with database connectivity checks
- **Comprehensive Health CLI Command** with environment, database, storage, and cache validation
- **Comprehensive Logging** with PSR-3 compatible logger
- **JSON File Logging** with context (request ID, exceptions, traces)
- **Runtime Error Tracking** with detailed exception information

### Console & CLI

- **Full-Featured CLI** with command registration system
- **Code Generators** for controllers, migrations, seeders, middleware
- **Project Scaffolding** with `create:project` command for new applications
- **Database Management** commands (migrate, seed, rollback)
- **Development Server** with configurable host/port
- **Cache Management** and route listing
- **Application Key Generation**

---

## 📁 Architecture

```text
project-root/
├── bin/
│   ├── console           # Main CLI entrypoint
│   ├── console.bat       # Windows batch file
│   ├── db-setup          # Database setup script
│   ├── migrate           # Migration runner
│   └── seed              # Seeder runner
├── bootstrap/
│   ├── config.php        # Configuration loader
│   ├── dependencies.php  # DI container setup
│   ├── routes.php        # Route registration
│   └── middleware.php    # Middleware stack
├── config/
│   └── settings.php      # Application configuration
├── migrations/           # Database migrations
├── seeders/              # Database seeders
├── public/
│   ├── index.php         # Front controller
│   ├── .htaccess         # Apache rewrite rules
│   └── assets/           # Static assets
├── src/
│   ├── Application/      # Application layer
│   │   ├── Command/      # Command bus & handlers
│   │   ├── Query/        # Query bus & handlers
│   │   └── DTO/          # Data transfer objects
│   ├── Domain/           # Domain layer
│   │   ├── Model/        # Entities & value objects
│   │   ├── Repository/   # Repository interfaces
│   │   ├── Event/        # Domain events
│   │   └── Service/      # Domain services
│   ├── Infrastructure/   # Infrastructure layer
│   │   ├── Console/      # CLI commands & application
│   │   ├── Database/     # Database helpers
│   │   ├── DI/           # Dependency injection
│   │   ├── Event/        # Event handling
│   │   ├── Health/       # Health checks
│   │   ├── Http/         # HTTP layer
│   │   ├── Logging/      # Logging implementation
│   │   ├── Middleware/   # Middleware classes
│   │   ├── Persistence/  # ORM & query builder
│   │   ├── RateLimit/    # Rate limiting
│   │   ├── Routing/      # Router & attributes
│   │   ├── Security/     # Security services
│   │   ├── Serialization/ # Auto-serialization
│   │   ├── Service/      # Infrastructure services
│   │   ├── Storage/      # File storage
│   │   ├── Templating/   # Template engine
│   │   └── Validation/   # Validation system
│   └── Presentation/     # Presentation layer
│       └── Controller/   # HTTP controllers
├── storage/
│   ├── cache/            # Template & application cache
│   ├── database/         # SQLite database files
│   └── uploads/          # File uploads
├── views/                # Template files
├── logs/                 # Log files
└── vendor/               # Composer dependencies
```

---

## � Create New Projects

MiniFramework PHP includes powerful scaffolding tools to create new projects quickly:

### Using CLI Command (from framework directory)

```bash
# Create a basic project
php bin/console create:project my-new-project

# Create with custom options
php bin/console create:project my-api --path=/var/www/my-api --namespace=MyApi
```

### Using Standalone Script

```bash
# PHP script (cross-platform)
php create-miniframework-project.php my-project

# PowerShell script (Windows)
.\create-miniframework-project.ps1 "my-project"

# Batch file (Windows)
create-miniframework-project.bat my-project
```

**What it creates automatically:**

- ✅ Complete project structure with proper architecture
- ✅ Updated namespaces and composer.json
- ✅ Environment configuration templates
- ✅ Git repository with appropriate .gitignore
- ✅ Installed dependencies
- ✅ Generated README with project-specific instructions

📖 **[Detailed Generator Documentation](GENERATOR_README.md)**

---

## �🛠️ Installation

### Requirements

- PHP 8.4+
- Composer
- SQLite (included with PHP)

### Quick Start

1. **Clone the repository**

   ```bash
   git clone https://github.com/NastMz/mini-php-framework.git
   cd mini-php-framework
   ```

2. **Install dependencies**

   ```bash
   composer install
   ```

3. **Generate application key**

   ```bash
   php bin/console key:generate
   ```

4. **Initialize database**

   ```bash
   php bin/console db:init
   php bin/console migrate
   ```

5. **Start development server**

   ```bash
   php bin/console serve
   ```

6. **Visit your application**
   Open `http://localhost:8000` in your browser

### Production Setup

1. **Configure environment**

   - Edit `config/settings.php` with production values
   - Set up environment variables for sensitive data

2. **Set up web server**

   - Point DocumentRoot to `public/` directory
   - Enable URL rewriting (Apache: `mod_rewrite`)

3. **Optimize for production**

   ```bash
   composer install --no-dev --optimize-autoloader
   php bin/console cache:clear
   ```

---

## 🚀 Usage

### Basic Routing with Attributes

```php
<?php
use App\Infrastructure\Routing\Attributes\Route;
use App\Infrastructure\Routing\Attributes\Controller;
use App\Infrastructure\Routing\HttpMethod;

#[Controller(prefix: '/api/v1', middleware: ['rate_limit'])]
class UserController
{
    #[Route(HttpMethod::GET, '/users', name: 'users.index')]
    public function index(): ResponseInterface
    {
        return (new Response())
            ->withHeader('Content-Type', 'application/json')
            ->write(json_encode(['users' => $this->userService->all()]));
    }

    #[Route(HttpMethod::POST, '/users', name: 'users.store')]
    public function store(
        #[Required] #[Email] string $email,
        #[Required] #[MinLength(3)] string $name
    ): ResponseInterface {
        // Validation is automatic - if we reach here, data is valid
        $user = $this->userService->create($email, $name);
        return (new Response())->withStatus(201)->write(json_encode($user));
    }
}
```

### CQRS Pattern

```php
<?php
// Command
class CreateUserCommand implements CommandInterface
{
    public function __construct(
        public readonly string $email,
        public readonly string $name,
        public readonly string $password
    ) {}
}

// Command Handler
class CreateUserCommandHandler implements CommandHandlerInterface
{
    public function handle(CommandInterface $command): mixed
    {
        // Business logic here
        return $this->userRepository->create($command->email, $command->name);
    }
}

// Usage in Controller
#[Route(HttpMethod::POST, '/users', name: 'users.create')]
public function create(
    #[Required] #[Email] string $email,
    #[Required] string $name,
    #[Required] string $password
): ResponseInterface {
    $command = new CreateUserCommand($email, $name, $password);
    $result = $this->commandBus->dispatch($command);

    return (new Response())
        ->withStatus(201)
        ->write(json_encode($result));
}
```

### Database Operations

```php
<?php
// Using Entity Manager
$user = new User('John Doe', 'john@example.com', 'password_hash');
$this->entityManager->persist($user);
$this->entityManager->flush();

// Using Query Builder
$users = $this->queryBuilder
    ->select('*')
    ->from('users')
    ->where('active', '=', 1)
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->execute()
    ->fetchAll();

// Using Transactions
$this->entityManager->transaction(function() {
    $user = new User('Jane Doe', 'jane@example.com', 'password_hash');
    $this->entityManager->persist($user);

    $profile = new UserProfile($user->getId(), 'Bio here');
    $this->entityManager->persist($profile);
});
```

---

## 🔐 JWT Authentication

The framework includes a complete JWT authentication system for API endpoints.

### Available Authentication Endpoints

- **POST /auth/login** - Login with email/password to receive JWT token
- **POST /auth/logout** - Logout endpoint (stateless JWT system)
- **GET /auth/me** - Get current user information (requires JWT token)
- **POST /auth/refresh** - Refresh JWT token (requires valid JWT token)

### Demo Credentials

For testing purposes, the following hardcoded credentials are available:

- **Email**: `admin@example.com`
- **Password**: `password123`

### Usage Examples

**Login:**

```bash
curl -X POST http://localhost:8000/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"password123"}'
```

**Response:**

```json
{
  "success": true,
  "message": "Login successful",
  "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "user": {
    "id": 1,
    "email": "admin@example.com",
    "role": "admin"
  },
  "expires_in": 3600
}
```

**Using JWT Token:**

```bash
curl -X GET http://localhost:8000/auth/me \
  -H "Authorization: Bearer your-jwt-token-here"
```

**Response:**

```json
{
  "success": true,
  "user": {
    "id": 1,
    "email": "admin@example.com",
    "role": "admin",
    "token_issued_at": "2025-07-05 17:27:13",
    "token_expires_at": "2025-07-05 18:27:13"
  }
}
```

**Protecting Routes:**

```php
// Routes protected by JWT middleware will automatically validate tokens
#[Route(HttpMethod::GET, '/api/protected', name: 'protected.endpoint')]
public function protectedEndpoint(RequestInterface $request): ResponseInterface
{
    // User information is automatically injected by the JWT middleware
    $userId = $request->getAttribute('auth_user_id');
    $userEmail = $request->getAttribute('auth_user_email');

    return new JsonResponse(['user' => $userId, 'email' => $userEmail]);
}
```

### JWT Configuration

Generate a secure JWT secret:

```bash
php bin/console jwt:secret
```

Add to your environment configuration:

```env
JWT_SECRET=your-generated-secret-here
```

### Testing Interface

The framework includes a **fully functional** built-in testing interface at `/jwt-test` that demonstrates all JWT authentication features:

- **Interactive Web Interface**: Complete CSP-compliant UI with external CSS/JS files
- **Live Token Testing**: Test all endpoints directly from the browser
- **Demo Credentials**: Pre-filled with working credentials for immediate testing
- **Real-time Results**: See actual API responses and token handling
- **Template Engine Demo**: Showcases the framework's templating system with sections and yields

Visit `http://localhost:8000/jwt-test` after starting the development server to try the authentication system.

---

## 🔧 CLI Commands

### Code Generation

```bash
# Generate controllers
php bin/console make:controller UserController
php bin/console make:controller Api/ProductController

# Generate migrations
php bin/console make:migration create_users_table
php bin/console make:migration add_email_to_users

# Generate seeders
php bin/console make:seeder UserSeeder
php bin/console make:seeder DatabaseSeeder

# Generate middleware
php bin/console make:middleware AuthMiddleware
php bin/console make:middleware RateLimitMiddleware
```

### Database Management

```bash
# Initialize database
php bin/console db:init

# Run migrations
php bin/console migrate
php bin/console migrate --fresh
php bin/console migrate --rollback

# Run seeders
php bin/console seed
php bin/console seed --class=UserSeeder

# Complete database setup
php bin/db-setup
```

### Development Tools

```bash
# Start development server
php bin/console serve
php bin/console serve --host=127.0.0.1 --port=8080

# List all routes
php bin/console route:list

# Check application health
php bin/console health:check:check

# Clear cache
php bin/console cache:clear

# Generate application key
php bin/console key:generate

# Generate JWT secret
php bin/console jwt:secret
```

### Windows Support

Windows users can use the provided batch files:

```cmd
bin\console.bat serve
bin\console.bat make:controller UserController
bin\console.bat migrate
```

### Composer Scripts

```bash
composer serve          # Start development server
composer migrate         # Run migrations
composer cache:clear     # Clear cache
composer key:generate    # Generate application key
composer test           # Run tests
```

---

## ⚠️ What's NOT Implemented

To maintain transparency about this learning project, here are notable features that are **not implemented**:

- **Authorization Middleware**: No role-based access control beyond basic JWT authentication
- **Database Relationships**: ORM doesn't support complex relationships
- **Migration Rollbacks**: Limited migration system
- **Caching Layer**: No Redis or advanced caching
- **Message Queues**: No async job processing
- **Email Service**: No mail sending capabilities
- **Testing Suite**: Limited test coverage
- **Production Optimizations**: Not performance-optimized
- **Advanced User Management**: No user registration, password reset, or account management

This framework focuses on core patterns and architecture rather than production features.

---

## 🌟 Advanced Features

### Middleware Stack

The framework provides conditional middleware stacks:

```php
// For web requests
- RequestIdMiddleware
- ErrorHandlerMiddleware
- CorsMiddleware
- SecurityHeadersMiddleware
- RateLimitMiddleware
- SessionMiddleware (web only)
- CsrfMiddleware (web only)
- AutoValidationMiddleware
- AutoSerializationMiddleware

// For API requests
- Same stack but without SessionMiddleware and CsrfMiddleware
```

### Auto-Registration

The framework automatically registers:

- Controllers in `src/Presentation/Controller/`
- Services in `src/Infrastructure/Service/`
- Command handlers in `src/Application/Command/Handlers/`
- Query handlers in `src/Application/Query/Handlers/`
- Event subscribers in `src/Infrastructure/Event/`

### Configuration

All configuration is centralized in `config/settings.php`:

```php
return [
    'app' => [
        'name' => 'MiniFramework',
        'env' => 'production',
        'debug' => false,
        'key' => 'your-app-key',
    ],
    'database' => [
        'dsn' => 'sqlite:storage/database/app.sqlite',
    ],
    'rate_limit' => [
        'max_requests' => 60,
        'window_size' => 60,
    ],
    'security' => [
        'headers' => [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
        ],
    ],
    'cors' => [
        'origins' => ['http://localhost:3000'],
        'methods' => ['GET', 'POST', 'PUT', 'DELETE'],
        'headers' => ['Content-Type', 'Authorization'],
    ],
];
```

---

## 🤝 Contributing

This is primarily a personal learning project, but if you find it educational and want to contribute improvements or suggestions:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/improvement`)
3. Make your changes with clear documentation
4. Add tests if applicable
5. Ensure code follows PSR-12 standards
6. Commit with descriptive messages (`git commit -m 'Add educational feature'`)
7. Push to the branch (`git push origin feature/improvement`)
8. Open a Pull Request with explanation of the learning value

### Learning Guidelines

- Focus on educational value over performance optimization
- Document design decisions and architectural choices
- Include comments explaining complex patterns
- Prioritize code clarity and learning opportunities
- Keep the academic/experimental nature intact

---

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

---

## 🙏 Acknowledgments

- **Personal Learning Project**: Created as an academic exercise to understand framework internals
- Built with modern PHP 8.4+ features for educational purposes
- Inspired by Laravel, Symfony, and other established PHP frameworks
- Designed to explore DDD, Clean Architecture, and modern PHP patterns
- **Not intended for production use** - this is a learning experiment

---

**MiniFramework PHP** - A personal exploration of modern PHP framework development.
