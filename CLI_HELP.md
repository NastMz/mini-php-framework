# MiniFramework CLI Helper

## Make Commands (para generar código)

### Controllers

```bash
php bin/console make:controller UserController
php bin/console make:controller Api/ProductController
php bin/console make:controller Admin/DashboardController
```

### Migrations

```bash
php bin/console make:migration create_users_table
php bin/console make:migration add_email_to_users_table
php bin/console make:migration create_products_table
```

### Seeders

```bash
php bin/console make:seeder UserSeeder
php bin/console make:seeder ProductSeeder
php bin/console make:seeder DatabaseSeeder
```

### Middleware

```bash
php bin/console make:middleware AuthMiddleware
php bin/console make:middleware RateLimitMiddleware
php bin/console make:middleware CorsMiddleware
```

## Database Commands

### Migrations

```bash
# Ejecutar migraciones pendientes
php bin/console migrate

# Ejecutar migraciones desde cero (elimina todas las tablas)
php bin/console migrate --fresh

# Revertir la última migración
php bin/console migrate --rollback
```

### Configuración completa de DB

```bash
# Ejecutar migraciones + seeders
php bin/db-setup
```

## Development Server

```bash
# Servidor en localhost:8000
php bin/console serve

# Servidor en host y puerto específicos
php bin/console serve --host=127.0.0.1 --port=8080
php bin/console serve -H 0.0.0.0 -p 3000
```

## Utilities

### Cache

```bash
# Limpiar cache
php bin/console cache:clear
```

### Application Key

```bash
# Generar y guardar clave en .env
php bin/console key:generate

# Solo mostrar la clave
php bin/console key:generate --show
```

### Routes

```bash
# Listar todas las rutas registradas
php bin/console route:list
```

## Composer Scripts (shortcuts)

```bash
composer serve          # = php bin/console serve
composer migrate         # = php bin/console migrate
composer run cache:clear # = php bin/console cache:clear
composer run key:generate # = php bin/console key:generate
```

## Para Windows

Puedes usar el archivo `.bat`:

```cmd
bin\console.bat help
bin\console.bat make:controller UserController
bin\console.bat serve
```

## Ejemplos de uso común

### Crear un módulo completo

```bash
# 1. Crear controlador
php bin/console make:controller ProductController

# 2. Crear migración
php bin/console make:migration create_products_table

# 3. Crear seeder
php bin/console make:seeder ProductSeeder

# 4. Ejecutar migración
php bin/console migrate

# 5. Ejecutar seeder
php bin/seed
```

### Desarrollo diario

```bash
# Limpiar cache
php bin/console cache:clear

# Iniciar servidor
php bin/console serve

# En otra terminal, ejecutar migraciones
php bin/console migrate
```
