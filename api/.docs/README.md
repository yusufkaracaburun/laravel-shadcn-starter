# API Documentation

Welcome to the Laravel API documentation. This directory contains comprehensive guides for configuring and using the API backend.

## Table of Contents

- [Overview](#overview)
- [Quick Start](#quick-start)
- [Authentication](#authentication)
- [Documentation Guides](#documentation-guides)
- [Development](#development)
- [Production Deployment](#production-deployment)

## Overview

This is a Laravel 12 API backend designed to work with separate frontend applications (SPAs). The API uses:

- **Laravel Sanctum**: For SPA authentication (cookie-based sessions)
- **Laravel Fortify**: For authentication routes (`/login`, `/logout`, `/register`)
- **Laravel Reverb**: For WebSocket/real-time features
- **Spatie Permissions**: For role-based access control

### Architecture

```
┌─────────────┐         ┌─────────────┐
│   Frontend  │ ──────> │   Backend   │
│   (Vue.js)  │ <────── │  (Laravel)  │
│             │         │             │
└─────────────┘         └─────────────┘
     :5173                    :8000
```

- **Frontend**: Separate Vue.js SPA application
- **Backend**: Laravel API (this repository)
- **Authentication**: Cross-domain cookie-based sessions
- **Communication**: RESTful API + WebSockets (Reverb)

## Quick Start

### 1. Environment Setup

Copy the example environment file and configure it:

```bash
cd api
cp .env.example .env
php artisan key:generate
```

### 2. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 3. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

### 4. Run Tests

```bash
php artisan test
```

## Authentication

This API uses **Laravel Sanctum** for SPA authentication with **Laravel Fortify** for authentication routes.

### Authentication Method

- **Type**: Cookie-based session authentication (Sanctum SPA mode)
- **Routes**: Handled by Fortify (`/login`, `/logout`, `/register`)
- **Protection**: CSRF-protected stateful authentication
- **Cross-Domain**: Fully supported

### Quick Authentication Setup

1. **Configure environment variables** (see [Sanctum Setup Guide](./sanctum.md)):
   ```env
   SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000
   SESSION_DOMAIN=
   CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
   ```

2. **Frontend makes authentication request**:
   ```typescript
   // 1. Get CSRF cookie
   await axios.get('/sanctum/csrf-cookie');
   
   // 2. Login
   await axios.post('/login', {
     email: 'user@example.com',
     password: 'password'
   });
   ```

3. **Access protected routes**:
   ```typescript
   // Automatically includes session cookies
   const user = await axios.get('/api/user/current');
   ```

### Protected Routes

All API routes under `/api/*` require authentication via `auth:sanctum` middleware:

- `GET /api/user/current` - Get current authenticated user
- `GET /api/user` - List users (with permissions)
- `POST /api/user` - Create user (with permissions)
- `GET /api/teams` - List teams
- `POST /api/teams` - Create team
- etc.

### Detailed Authentication Guide

For complete authentication setup, configuration, and troubleshooting, see:

📖 **[Sanctum & Fortify Setup Guide](./sanctum.md)**

This guide covers:
- Complete environment configuration
- Local development setup
- Production deployment
- Troubleshooting common issues
- Frontend integration examples

## Documentation Guides

### Core Documentation

- **[Sanctum & Fortify Setup](./sanctum.md)** - Complete authentication setup guide
  - Environment configuration
  - Cross-domain setup
  - Local and production deployment
  - Troubleshooting

- **[Caching Configuration](./caching.md)** - API response caching
  - Cache strategies
  - Cache invalidation
  - Performance optimization

- **[Reverb Setup](./reverb.md)** - WebSocket/real-time features
  - Broadcasting configuration
  - Frontend integration
  - Production setup

## Development

### Project Structure

```
api/
├── app/
│   ├── Actions/          # Fortify actions (CreateUser, etc.)
│   ├── Http/
│   │   ├── Controllers/  # API controllers
│   │   └── Middleware/    # Custom middleware
│   ├── Models/           # Eloquent models
│   └── Providers/       # Service providers
├── config/               # Configuration files
├── routes/               # Route definitions
│   ├── api.php          # API routes
│   ├── auth.php         # Auth routes
│   └── web.php          # Web routes
├── tests/               # Test files
└── .docs/              # Documentation (this directory)
```

### Key Configuration Files

- `config/sanctum.php` - Sanctum configuration
- `config/fortify.php` - Fortify configuration
- `config/cors.php` - CORS configuration
- `config/session.php` - Session configuration
- `bootstrap/app.php` - Application bootstrap & middleware

### Running Tests

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/Api/AuthenticationTest.php

# Run with coverage
php artisan test --coverage

# Run with filter
php artisan test --filter=Authentication
```

### Code Quality

```bash
# Format code
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse

# Type checking
vendor/bin/rector process --dry-run
```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Update all environment variables for production
- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure `SANCTUM_STATEFUL_DOMAINS` with production domains
- [ ] Set `SESSION_SECURE_COOKIE=true` (HTTPS required)
- [ ] Configure `CORS_ALLOWED_ORIGINS` with production frontend domain
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Run `php artisan view:cache` (if using views)
- [ ] Test authentication flow in production environment

### Environment Variables

See the [Sanctum Setup Guide](./sanctum.md#production-configuration) for complete production environment configuration.

### Deployment Steps

1. **Update environment variables** in production `.env` file
2. **Clear and cache configuration**:
   ```bash
   php artisan config:clear
   php artisan config:cache
   php artisan route:cache
   ```
3. **Run migrations** (if needed):
   ```bash
   php artisan migrate --force
   ```
4. **Test authentication flow**:
   - Verify CSRF cookie endpoint
   - Test login/register
   - Verify authenticated API requests

## API Endpoints

### Authentication Endpoints

- `GET /sanctum/csrf-cookie` - Get CSRF protection cookie
- `POST /login` - Authenticate user
- `POST /logout` - Logout user
- `POST /register` - Register new user
- `POST /forgot-password` - Request password reset
- `POST /reset-password` - Reset password

### User Endpoints

- `GET /api/user/current` - Get current authenticated user
- `GET /api/user` - List users (paginated)
- `POST /api/user` - Create user
- `GET /api/user/{id}` - Get user by ID
- `PUT /api/user/{id}` - Update user
- `DELETE /api/user/{id}` - Delete user

### Team Endpoints

- `GET /api/teams` - List teams
- `POST /api/teams` - Create team
- `GET /api/teams/{id}` - Get team by ID
- `PUT /api/teams/{id}` - Update team
- `DELETE /api/teams/{id}` - Delete team
- `PUT /api/current-team` - Switch current team

### Health Check

- `GET /up` - API health check endpoint

## Troubleshooting

### Common Issues

1. **419 CSRF Token Mismatch**
   - See [Sanctum Setup Guide - Troubleshooting](./sanctum.md#419-csrf-token-mismatch)

2. **Cookies Not Being Set**
   - See [Sanctum Setup Guide - Troubleshooting](./sanctum.md#cookies-not-being-set)

3. **CORS Errors**
   - See [Sanctum Setup Guide - Troubleshooting](./sanctum.md#cors-errors)

4. **Session Not Persisting**
   - See [Sanctum Setup Guide - Troubleshooting](./sanctum.md#session-not-persisting)

### Getting Help

1. Check the relevant documentation guide
2. Review the troubleshooting sections
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify environment configuration
5. Run tests to verify setup: `php artisan test`

## Additional Resources

- [Laravel Documentation](https://laravel.com/docs/12.x)
- [Laravel Sanctum Documentation](https://laravel.com/docs/12.x/sanctum)
- [Laravel Fortify Documentation](https://laravel.com/docs/12.x/fortify)
- [Laravel Reverb Documentation](https://laravel.com/docs/12.x/reverb)

---

**Last Updated**: 2025-01-30

