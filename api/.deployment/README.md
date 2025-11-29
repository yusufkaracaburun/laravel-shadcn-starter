# Docker Setup for Laravel API

This Docker setup uses **Docker Compose profiles** to manage different environments (development, staging, production) in a single `docker-compose.yml` file.

## Quick Start

### Development (Default Profile)

```bash
# From the api/ directory
make dev
# or
docker-compose -f .deployment/docker-compose.yml --profile dev up -d

# Access API at http://localhost:8000
```

### Staging Environment

```bash
# From the api/ directory
make staging
# or
docker-compose -f .deployment/docker-compose.yml --profile staging up -d

# Access API at http://localhost:8001
```

### Production Environment

```bash
# From the api/ directory
make prod
# or
docker-compose -f .deployment/docker-compose.yml --profile prod up -d

# Access API at http://localhost:80
```

## Docker Compose Profiles

The setup uses three profiles:

### 1. **dev** (Default)
- **Database**: SQLite (quick setup)
- **Dependencies**: All dev dependencies included
- **Debugging**: Enabled
- **Hot Reloading**: Yes (volume mounting)
- **Port**: 8000
- **Services**: API only

### 2. **staging**
- **Database**: MySQL 8.0
- **Cache/Session**: Redis
- **Dependencies**: Production build (no dev deps)
- **Debugging**: Enabled (for testing)
- **Port**: 8001
- **Services**: API, MySQL, Redis

### 3. **prod**
- **Database**: MySQL 8.0
- **Cache/Session**: Redis
- **Dependencies**: Production build (optimized)
- **Debugging**: Disabled
- **Port**: 80
- **Services**: API, MySQL, Redis

## Files Overview

All Docker-related files are located in the `.deployment/` directory:

- **Dockerfile** - Multi-stage production build optimized for FrankenPHP
- **Dockerfile.dev** - Development build with dev dependencies
- **docker-compose.yml** - Single file with profiles for all environments
- **.dockerignore** - Files excluded from Docker build context
- **Makefile** - Convenient commands for common tasks (located in `api/` directory)
- **nginx.conf** - Nginx configuration (optional)

## Using Profiles

### Command Line

```bash
# From api/ directory

# Development (default profile)
docker-compose -f .deployment/docker-compose.yml --profile dev up -d

# Staging
docker-compose -f .deployment/docker-compose.yml --profile staging up -d

# Production
docker-compose -f .deployment/docker-compose.yml --profile prod up -d

# Multiple profiles
docker-compose -f .deployment/docker-compose.yml --profile dev --profile staging up -d
```

### Using Makefile

```bash
# From api/ directory
make dev              # Start development
make staging          # Start staging
make prod             # Start production

make logs             # Development logs
make logs-staging     # Staging logs
make logs-prod        # Production logs

make shell            # Development shell
make shell-staging    # Staging shell
make shell-prod       # Production shell

make migrate          # Development migrations
make migrate-staging  # Staging migrations
make migrate-prod     # Production migrations

make down             # Stop all
make down-dev         # Stop development only
make down-staging     # Stop staging only
make down-prod        # Stop production only
```

## Local Development

### Development Profile (SQLite)

```bash
# From api/ directory
make dev

# Or manually
docker-compose -f .deployment/docker-compose.yml --profile dev up -d
```

**Features:**
- SQLite database (no separate DB container needed)
- All dev dependencies
- Hot reloading with volume mounts
- Debug mode enabled

### Staging Profile (MySQL + Redis)

```bash
# From api/ directory
make staging

# Set environment variables
export DB_PASSWORD=your-password
export DB_DATABASE=laravel
export DB_USERNAME=laravel
```

**Features:**
- MySQL 8.0 database
- Redis for cache/sessions
- Production build but with debugging enabled
- Separate ports to avoid conflicts

## Production Deployment

### Build Production Image

```bash
# From api/ directory
make build
# or
docker build -f .deployment/Dockerfile -t laravel-api:latest .
```

### Run with Docker Compose

```bash
# From api/ directory
make prod

# Set required environment variables first
export DB_PASSWORD=your-secure-password
export APP_URL=https://your-domain.com
```

### Standalone Container

```bash
docker run -d \
  --name laravel-api \
  -p 80:80 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e APP_KEY=your-app-key \
  -e DB_CONNECTION=mysql \
  -e DB_HOST=your-db-host \
  -e DB_DATABASE=laravel \
  -e DB_USERNAME=laravel \
  -e DB_PASSWORD=secret \
  -v $(pwd)/storage:/var/www/html/storage \
  laravel-api:latest
```

## Profile Comparison

| Feature | dev | staging | prod |
|---------|-----|---------|------|
| Database | SQLite | MySQL | MySQL |
| Cache | File | Redis | Redis |
| Sessions | File | Redis | Redis |
| Dev Dependencies | ✅ | ❌ | ❌ |
| Debug Mode | ✅ | ✅ | ❌ |
| Hot Reloading | ✅ | ❌ | ❌ |
| Port | 8000 | 8001 | 80 |
| Optimization | ❌ | ✅ | ✅ |

## Environment Variables

### Development Profile

```bash
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=sqlite
DB_DATABASE=database/database.sqlite
```

### Staging Profile

```bash
APP_ENV=staging
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=mysql
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=password
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Production Profile

```bash
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=mysql
DB_HOST=mysql-prod
DB_DATABASE=laravel
DB_USERNAME=laravel
DB_PASSWORD=your-secure-password
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

## Common Tasks

### View Logs

```bash
# Development
make logs
# or
docker-compose -f .deployment/docker-compose.yml --profile dev logs -f api

# Staging
make logs-staging

# Production
make logs-prod
```

### Run Migrations

```bash
# Development
make migrate

# Staging
make migrate-staging

# Production
make migrate-prod
```

### Access Container Shell

```bash
# Development
make shell

# Staging
make shell-staging

# Production
make shell-prod
```

### Run Tests

```bash
make test
# Runs tests in development container
```

### Optimize for Production

```bash
make optimize
# Optimizes production container
```

## Health Check

All profiles include a health check endpoint at `/up`:

```bash
# Development
curl http://localhost:8000/up

# Staging
curl http://localhost:8001/up

# Production
curl http://localhost/up
```

## Troubleshooting

### Permission Issues

```bash
# Development
docker-compose -f .deployment/docker-compose.yml --profile dev exec api chown -R www-data:www-data storage bootstrap/cache

# Staging
docker-compose -f .deployment/docker-compose.yml --profile staging exec api-staging chown -R www-data:www-data storage bootstrap/cache

# Production
docker-compose -f .deployment/docker-compose.yml --profile prod exec api-prod chown -R www-data:www-data storage bootstrap/cache
```

### Clear Cache

```bash
# Development
docker-compose -f .deployment/docker-compose.yml --profile dev exec api php artisan optimize:clear

# Staging/Production
docker-compose -f .deployment/docker-compose.yml --profile staging exec api-staging php artisan optimize:clear
```

### Rebuild Containers

```bash
# Development
docker-compose -f .deployment/docker-compose.yml --profile dev build --no-cache

# Staging
docker-compose -f .deployment/docker-compose.yml --profile staging build --no-cache

# Production
docker-compose -f .deployment/docker-compose.yml --profile prod build --no-cache
```

### Stop All Profiles

```bash
make down
# Stops all containers from all profiles
```

## Benefits of Profiles

1. **Single Configuration File** - All environments in one place
2. **Easy Switching** - Change environments with a single flag
3. **Isolated Services** - Each profile has its own containers
4. **No Conflicts** - Different ports for each profile
5. **Flexible** - Run multiple profiles simultaneously if needed

## References

- [Laravel Deployment Documentation](https://laravel.com/docs/12.x/deployment)
- [Docker Compose Profiles](https://docs.docker.com/compose/profiles/)
- [FrankenPHP Documentation](https://frankenphp.dev/)
- [Laravel Octane Documentation](https://laravel.com/docs/octane)
