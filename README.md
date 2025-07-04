# Custom PHP Micro-Framework

A 100% home-grown PHP micro-framework template implementing DDD and Clean Architecture. Ready to clone and extend—just add your use cases, controllers, views and you’re good to go!

---

## Features

- **PSR-4 Autoloading** with Composer
- **Modular bootstrapping** (config, dependencies, routes, middleware)
- **Dependency Injection** via PHP-DI–style container
- **Robust Router** with HTTP-method enums, parameterized routes and auto-dispatch
- **HTTP Layer**
  - Custom `RequestInterface` / `ResponseInterface`
  - Global error-handler middleware
  - CORS, security headers, CSRF protection, rate-limiting, HTTP caching
- **Middleware pipeline** (own PSR-15–like implementation)
- **Mini-ORM**
  - Fluent `QueryBuilder` (SELECT, INSERT, UPDATE, DELETE, WHERE, etc.)
  - `EntityManager` with PHP 8 Attributes (`#[Table]`, `#[Column]`)
  - ACID transactions with `transaction()` helper
- **Database tooling**
  - Migrations runner (`migrations/`)
  - Seeders runner (`seeders/`)
  - Combined `bin/db-setup` for migrate + seed
- **Validation**
  - `ValidatorInterface`, built-in rules (`NotEmpty`, `MinLength`)
  - `ValidationMiddleware` returning 422 with field-level errors
- **Authentication & Authorization**
  - Password hashing (`BcryptHasher`)
  - JWT tokens (`JwtService`)
  - `AuthenticationMiddleware`, `AuthorizationMiddleware`
- **Domain Events**
  - `DomainEventInterface`, `DomainEventDispatcher`
  - Subscribe with `DomainEventSubscriberInterface`
- **Command Bus**
  - `CommandInterface`, `CommandHandlerInterface`, `CommandBusInterface`
  - `InMemoryCommandBus` with handler mapping
- **Templating Engine**
  - Own `TemplateEngine` supporting layouts, sections, auto-escaping and cache
- **File Uploads**
  - `FileStorageInterface` abstraction (local storage impl.)
  - `FileUploadService` with size/type validation
- **Health Checks & Metrics**
  - `/healthz` endpoint via `HealthCheckService` (DB + uptime)
- **Logging**
  - PSR-3–like `LoggerInterface`
  - JSON file logger with context (request ID, exceptions, etc.)
- **Console & Scheduler**
  - Single `bin/console` entrypoint
  - `ConsoleKernel`, commands, handlers
  - Embedded `Scheduler` for daily jobs

---

## Directory Structure

```text

project-root/
├── bin/
│ ├── console # CLI entrypoint (commands & scheduler)
│ ├── db-setup # Migrate + seed runner
│ ├── migrate # Migrations runner
│ └── seed # Seeders runner
├── bootstrap/
│ ├── config.php # load config/settings.php
│ ├── dependencies.php # build DI container
│ ├── routes.php # define routes
│ └── middleware.php # assemble middleware stack
├── config/
│ └── settings.php # base configuration
├── migrations/ # Migration files (up/down)
├── seeders/ # Seeder classes
├── public/
│ ├── .htaccess # rewrite to index.php
│ └── index.php # front controller
├── src/
│ ├── Application/
│ │ ├── Command/ # Command Bus
│ │ ├── DTO/ # Request DTOs
│ │ └── UseCase/ # Legacy use-case classes
│ ├── Domain/
│ │ ├── Model/ # Entities, Value Objects
│ │ ├── Repository/ # Interfaces
│ │ └── Event/ # Domain Events
│ ├── Infrastructure/
│ │ ├── Console/ # CLI & Scheduler
│ │ ├── Http/ # Request/Response, ViewHelpers
│ │ ├── Logging/ # LoggerInterface/FileLogger
│ │ ├── Middleware/ # All middleware implementations
│ │ ├── Persistence/ # QueryBuilder, EntityManager, migrations, seeders
│ │ ├── RateLimit/ # RateLimitService
│ │ ├── Routing/ # Router, Route, HttpMethod
│ │ ├── Security/ # JWT, CsrfTokenManager
│ │ ├── Storage/ # FileStorage, FileUploadService
│ │ └── Templating/ # TemplateEngine
└── vendor/
```

---

## Installation

1. Clone the repo

   ```bash
   git clone <repo-url> project-root
   cd project-root
   ```

2. Install dependencies

   ```bash
   composer install
   ```

3. Configure your environment

   - Edit `config/settings.php` (DB DSN, upload dir/URL, rate-limit, JWT secret, etc.)
   - (Optional) Add `.env` loader in `bootstrap/config.php`

4. Prepare the database

   ```bash
   ./bin/db-setup
   ```

5. Point your web server to `public/`

   - Apache: enable `mod_rewrite`, set `DocumentRoot` → `.../public`
   - XAMPP/Windows: drop into `htdocs`, adjust hosts/vhosts

---

## Usage

### Run migrations only

```bash
./bin/migrate
```

### Run seeders only

```bash
./bin/seed
```

### CLI commands

```bash
# list all commands
./bin/console

# run migrations via console
./bin/console migrate:run

# run your custom command
./bin/console your:command arg1 arg2
```

### HTTP Endpoints

- `GET  /healthz` → Health check JSON
- `POST /login` → Authenticate (returns JWT)
- Protected routes require header `Authorization: Bearer <token>`
- CSRF-protected form routes need hidden `_csrf_token` field or `X-CSRF-Token` header

---

## CLI Commands

This framework includes a powerful CLI tool for development tasks:

### Available Commands

```bash
# Show help
php bin/console help

# Generate application key
php bin/console key:generate
php bin/console key:generate --show

# Make commands
php bin/console make:controller UserController
php bin/console make:migration create_users_table
php bin/console make:seeder UserSeeder
php bin/console make:middleware AuthMiddleware

# Database commands
php bin/console migrate
php bin/console migrate --fresh
php bin/console migrate --rollback

# Development server
php bin/console serve
php bin/console serve --host=127.0.0.1 --port=8080

# Cache management
php bin/console cache:clear

# Routes
php bin/console route:list
```

### Windows Users

For Windows users, you can use the `.bat` file:

```cmd
bin\console.bat help
bin\console.bat make:controller UserController
bin\console.bat serve
```

### Composer Scripts

Common commands are also available as Composer scripts:

```bash
composer serve          # Start development server
composer migrate         # Run migrations
composer run cache:clear # Clear cache
composer run key:generate # Generate app key
```

## Development

### Creating Controllers

```bash
php bin/console make:controller ProductController
```

This creates a new controller with basic CRUD methods in `src/Presentation/Controller/`.

### Creating Migrations

```bash
php bin/console make:migration create_products_table
```

This creates a new migration file in `migrations/` with timestamp prefix.

### Creating Seeders

```bash
php bin/console make:seeder ProductSeeder
```

This creates a new seeder class in `seeders/`.

### Creating Middleware

```bash
php bin/console make:middleware RateLimitMiddleware
```

This creates a new middleware class in `src/Infrastructure/Middleware/`.

---

## Contributing

1. Fork and clone
2. Create your feature branch (`git checkout -b feature/xyz`)
3. Commit your changes (`git commit -m 'Add xyz'`)
4. Push to the branch (`git push origin feature/xyz`)
5. Open a Pull Request

---

## License

MIT © Kevin Martinez
