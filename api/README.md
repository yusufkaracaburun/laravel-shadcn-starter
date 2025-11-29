<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting with Laravel Reverb](https://laravel.com/docs/12.x/reverb).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## Docker Deployment

This application includes Docker configuration with support for multiple environments using Docker Compose profiles.

### Quick Start

```bash
# From the api/ directory
make dev      # Start development environment (SQLite, port 8000)
make staging  # Start staging environment (MySQL + Redis, port 8001)
make prod     # Start production environment (MySQL + Redis, port 80)
```

### Available Commands

```bash
make help              # Show all available commands
make dev               # Start development
make staging           # Start staging
make prod              # Start production
make down              # Stop all containers
make logs              # View development logs
make shell             # Access development container shell
make test              # Run tests in development container
make migrate           # Run migrations in development
```

For detailed Docker documentation, see [.deployment/README.md](.deployment/README.md).

## Laravel Reverb (Real-time WebSocket Communication)

This application is configured with [Laravel Reverb](https://laravel.com/docs/12.x/reverb) for real-time WebSocket communication.

### Configuration

Reverb is configured in `config/reverb.php` and `config/broadcasting.php`. The default broadcaster is set to `reverb`.

### Environment Variables

**API `.env`** (`api/.env`):

```env
# Reverb Configuration
BROADCAST_CONNECTION=reverb
REVERB_APP_ID=your-app-id
REVERB_APP_KEY=your-app-key
REVERB_APP_SECRET=your-app-secret
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http
REVERB_ALLOWED_ORIGINS=http://localhost:5173,http://localhost:3000
```

**Client `.env`** (`client/.env`):

```env
# Reverb Configuration (must match API values)
VITE_REVERB_ENABLED=true
VITE_REVERB_APP_KEY=your-app-key          # Same as REVERB_APP_KEY above
VITE_REVERB_HOST=localhost                 # Same as REVERB_HOST above
VITE_REVERB_PORT=8080                      # Same as REVERB_PORT above
VITE_REVERB_SCHEME=http                    # Same as REVERB_SCHEME above
```

> **Note**: The client only needs the **public** credentials (`APP_KEY`, `HOST`, `PORT`, `SCHEME`). The secret and app ID stay in the API `.env` only. See [REVERB_SETUP.md](../REVERB_SETUP.md) for detailed setup instructions.

### Running Reverb Server

The Reverb server is automatically started when you run `composer run dev`. You can also start it manually:

```bash
php artisan reverb:start
```

### Broadcasting Events

Create events that implement `ShouldBroadcast`:

```php
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class ExampleBroadcastEvent implements ShouldBroadcast
{
    public function broadcastOn(): array
    {
        return [new Channel('example-channel')];
    }
}
```

Then broadcast the event:

```php
event(new ExampleBroadcastEvent('Hello from Reverb!'));
```

### Client-Side Usage

The Vue client is configured with Laravel Echo. Use the `useEcho()` composable:

```vue
<script setup>
import { useEcho } from '@/composables/use-echo'

const echo = useEcho()

if (echo) {
  echo.channel('example-channel')
    .listen('.example.event', (data) => {
      console.log('Received:', data)
    })
}
</script>
```

See `client/src/components/example-reverb-listener.vue` for a complete example.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
