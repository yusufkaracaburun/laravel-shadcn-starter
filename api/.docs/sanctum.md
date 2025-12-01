# Laravel Sanctum & Fortify API Authentication Setup

Complete guide for configuring Laravel Sanctum with Fortify for cross-domain SPA (Single Page Application) authentication.

## Table of Contents

- [Overview](#overview)
- [How It Works](#how-it-works)
- [Quick Start](#quick-start)
- [Environment Configuration](#environment-configuration)
- [Backend Configuration](#backend-configuration)
- [Frontend Configuration](#frontend-configuration)
- [Authentication Flow](#authentication-flow)
- [Testing](#testing)
- [Troubleshooting](#troubleshooting)
- [Production Deployment](#production-deployment)
- [References](#references)

## Overview

This application uses **Laravel Sanctum** for SPA authentication and **Laravel Fortify** for authentication routes. The setup supports:

- **Cross-domain authentication**: Frontend and backend can be on different domains/ports
- **Cookie-based sessions**: No API tokens needed for SPA authentication
- **CSRF protection**: Secure stateful authentication
- **CORS support**: Properly configured for cross-origin requests

### Architecture

- **Frontend**: Vue.js SPA (typically `localhost:5173` in development)
- **Backend**: Laravel API (typically `localhost:8000` in development)
- **Authentication Method**: Cookie-based session authentication (Sanctum SPA mode)
- **Authentication Routes**: Handled by Laravel Fortify (`/login`, `/logout`, `/register`)

## How It Works

### Sanctum SPA Authentication

Sanctum provides two authentication methods:

1. **SPA Authentication** (used here): Cookie-based session authentication
   - Uses Laravel's built-in session authentication
   - Requires CSRF protection
   - Works for first-party SPAs
   - No API tokens needed

2. **API Token Authentication**: Bearer token authentication
   - For third-party API access
   - Uses `Authorization: Bearer {token}` header
   - Not used for SPA authentication

### Authentication Flow

1. Frontend requests CSRF cookie: `GET /sanctum/csrf-cookie`
2. Frontend sends credentials: `POST /login` (with CSRF token)
3. Backend creates session and sets authentication cookies
4. Subsequent requests include session cookies automatically
5. Protected routes check session via `auth:sanctum` middleware

## Quick Start

### 1. Backend Setup

Ensure your `api/.env` file has the following variables:

```env
# Sanctum - Include both frontend and backend domains
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000

# Session - For cross-domain development
SESSION_DOMAIN=
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false

# Application URLs
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

# CORS - Frontend origins
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

### 2. Frontend Setup

Ensure your `client/.env` file has:

```env
VITE_SERVER_API_URL=http://localhost:8000
VITE_SERVER_API_PREFIX=/api
```

### 3. Verify Configuration

Run the backend tests to verify everything works:

```bash
cd api
php artisan test
```

## Environment Configuration

### Backend (`api/.env`)

#### Development Configuration

```env
# ============================================
# Sanctum Configuration
# ============================================
# Must include BOTH frontend AND backend domains/ports
# This tells Sanctum which domains should receive stateful auth cookies
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000

# ============================================
# Session Configuration
# ============================================
# For cross-domain development (localhost:5173 → localhost:8000):
# Set to empty string to allow cookies on both localhost and 127.0.0.1
# Note: Keeping 127.0.0.1 in SANCTUM_STATEFUL_DOMAINS for compatibility
SESSION_DOMAIN=

# For same-origin development (both on localhost):
# SESSION_DOMAIN=localhost

# Same-Site cookie attribute
# - 'lax': Default, works for most scenarios
# - 'none': Required for cross-domain (requires secure=true)
SESSION_SAME_SITE=lax

# Secure cookies (HTTPS only)
# - false: For HTTP in development
# - true: Required for HTTPS in production
SESSION_SECURE_COOKIE=false

# Session lifetime in minutes
SESSION_LIFETIME=120

# ============================================
# Application URLs
# ============================================
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:5173

# ============================================
# CORS Configuration
# ============================================
# Comma-separated list of allowed frontend origins
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

#### Production Configuration

```env
# ============================================
# Sanctum Configuration
# ============================================
# Include both frontend and backend domains (no ports needed for HTTPS)
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com,your-backend-domain.com

# ============================================
# Session Configuration
# ============================================
# For same domain: use .yourdomain.com (dot prefix for subdomain support)
# For different domains: may need SESSION_SAME_SITE=none
SESSION_DOMAIN=.your-domain.com

# For cross-domain: use 'none' with secure=true
SESSION_SAME_SITE=lax
# OR for different domains:
# SESSION_SAME_SITE=none

# Must be true in production (HTTPS required)
SESSION_SECURE_COOKIE=true

# ============================================
# Application URLs
# ============================================
APP_URL=https://your-backend-domain.com
FRONTEND_URL=https://your-frontend-domain.com

# ============================================
# CORS Configuration
# ============================================
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.com
```

### Frontend (`client/.env`)

#### Development Configuration

```env
VITE_SERVER_API_URL=http://localhost:8000
VITE_SERVER_API_PREFIX=/api
```

#### Production Configuration

```env
VITE_SERVER_API_URL=https://your-backend-domain.com
VITE_SERVER_API_PREFIX=/api
```

## Backend Configuration

### Key Configuration Files

#### 1. Sanctum Configuration (`config/sanctum.php`)

The Sanctum configuration is already set up correctly:

- **Stateful domains**: Configured via `SANCTUM_STATEFUL_DOMAINS` environment variable
- **Guard**: Uses `web` guard for session authentication
- **Middleware**: Properly configured for CSRF protection

#### 2. CORS Configuration (`config/cors.php`)

Already configured with:

- **Paths**: `api/*`, `sanctum/csrf-cookie`, `login`, `logout`, `register`
- **Supports credentials**: `true` (required for cookies)
- **Allowed origins**: Configurable via `CORS_ALLOWED_ORIGINS`

#### 3. Session Configuration (`config/session.php`)

- **Driver**: `database` (default)
- **Domain**: Configurable via `SESSION_DOMAIN`
- **Same-Site**: Configurable via `SESSION_SAME_SITE`
- **Secure**: Configurable via `SESSION_SECURE_COOKIE`

#### 4. CSRF Configuration (`bootstrap/app.php`)

- **API routes** (`api/*`): Excluded from CSRF (Bearer token auth)
- **Stateful routes** (`/login`, `/logout`, `/register`): Protected by CSRF
- **Sanctum CSRF cookie** (`/sanctum/csrf-cookie`): Accessible (GET request)

### Middleware Stack

The middleware is configured in `bootstrap/app.php`:

```php
// API middleware includes:
- HandleCors::class (handles CORS headers)
- StartSession::class (enables sessions for API routes)
- auth:sanctum (checks for session or Bearer token)
```

## Frontend Configuration

### Axios Setup

Your frontend axios configuration should include:

```typescript
// Required headers
axios.defaults.headers.common['Accept'] = 'application/json';
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Required for cookies
axios.defaults.withCredentials = true;

// Base URL configuration
axios.defaults.baseURL = import.meta.env.VITE_SERVER_API_URL;
```

### Authentication Flow Implementation

```typescript
// 1. Get CSRF cookie
await axios.get('/sanctum/csrf-cookie');

// 2. Login with credentials
await axios.post('/login', {
  email: 'user@example.com',
  password: 'password'
});

// 3. Subsequent requests automatically include session cookies
const response = await axios.get('/api/user/current');
```

## Authentication Flow

### Complete Flow Diagram

```
Frontend                    Backend
   |                           |
   |-- GET /sanctum/csrf-cookie -->|
   |<-- Set-Cookie: XSRF-TOKEN --|
   |                           |
   |-- POST /login ----------->|
   |   (with X-XSRF-TOKEN)     |
   |                           |
   |<-- Set-Cookie: session ---|
   |<-- 200 OK ----------------|
   |                           |
   |-- GET /api/user/current ->|
   |   (with session cookie)   |
   |                           |
   |<-- 200 OK + user data ----|
```

### Step-by-Step Process

1. **CSRF Cookie Request**
   ```http
   GET /sanctum/csrf-cookie HTTP/1.1
   Host: localhost:8000
   ```
   Response sets `XSRF-TOKEN` cookie

2. **Login Request**
   ```http
   POST /login HTTP/1.1
   Host: localhost:8000
   Cookie: XSRF-TOKEN=...
   X-XSRF-TOKEN: ...
   Content-Type: application/json
   
   {
     "email": "user@example.com",
     "password": "password"
   }
   ```
   Response sets session cookie

3. **Authenticated Request**
   ```http
   GET /api/user/current HTTP/1.1
   Host: localhost:8000
   Cookie: laravel_session=...
   ```
   Response returns user data

## Testing

### Development Testing Checklist

1. **Start both servers**:
   ```bash
   # Backend
   cd api
   php artisan serve
   
   # Frontend
   cd client
   npm run dev
   ```

2. **Test authentication flow**:
   - [ ] CSRF cookie is received from `/sanctum/csrf-cookie`
   - [ ] Login request succeeds without 419 error
   - [ ] XSRF-TOKEN cookie is set and sent in subsequent requests
   - [ ] Register endpoint works
   - [ ] Logout endpoint works
   - [ ] Authenticated API requests work (`/api/user/current`)
   - [ ] Works with both `localhost:5173` and `127.0.0.1:5173`

### Running Tests

```bash
cd api

# Run all tests
php artisan test

# Run authentication tests only
php artisan test --filter=Authentication

# Run with coverage
php artisan test --coverage
```

### Test Files

- `tests/Feature/Api/AuthenticationTest.php` - Basic authentication tests
- `tests/Feature/Api/AuthenticationFlowTest.php` - Complete flow tests
- `tests/Feature/Auth/LoginTest.php` - Login-specific tests
- `tests/Feature/Auth/RegisterTest.php` - Registration tests

## Troubleshooting

### 419 CSRF Token Mismatch

**Symptoms**: Login/register requests return 419 error

**Solutions**:

1. **Check SANCTUM_STATEFUL_DOMAINS**:
   ```bash
   # Must include both frontend and backend domains
   SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000
   ```

2. **Verify CSRF cookie is retrieved first**:
   ```typescript
   // Must call this before login/register
   await axios.get('/sanctum/csrf-cookie');
   ```

3. **Check headers are set correctly**:
   ```typescript
   axios.defaults.headers.common['Accept'] = 'application/json';
   axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
   ```

4. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

### Cookies Not Being Set

**Symptoms**: Session cookies not appearing in browser

**Solutions**:

1. **Check SESSION_DOMAIN**:
   - For cross-domain development (`localhost:5173` → `localhost:8000`): Set to empty string
   - For same origin (both on localhost): Set to `localhost`

2. **Check SESSION_SECURE_COOKIE**:
   - Development (HTTP): `false`
   - Production (HTTPS): `true`

3. **Verify withCredentials**:
   ```typescript
   axios.defaults.withCredentials = true;
   ```

4. **Check browser console** for cookie-related errors

### CORS Errors

**Symptoms**: Browser blocks requests due to CORS policy

**Solutions**:

1. **Check CORS_ALLOWED_ORIGINS**:
   ```env
   CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
   ```

2. **Verify CORS paths include required routes**:
   - `api/*`
   - `sanctum/csrf-cookie`
   - `login`, `logout`, `register`

3. **Check supports_credentials** is `true` in `config/cors.php`

4. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

### Session Not Persisting

**Symptoms**: User gets logged out after each request

**Solutions**:

1. **Check session driver**:
   ```env
   SESSION_DRIVER=database
   ```

2. **Verify sessions table exists**:
   ```bash
   php artisan migrate
   ```

3. **Check session lifetime**:
   ```env
   SESSION_LIFETIME=120
   ```

## Production Deployment

### Pre-Deployment Checklist

- [ ] Update `SANCTUM_STATEFUL_DOMAINS` with production domains
- [ ] Set `SESSION_DOMAIN` appropriately
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `APP_URL` to HTTPS URL
- [ ] Set `CORS_ALLOWED_ORIGINS` to production frontend domain
- [ ] Verify HTTPS is properly configured
- [ ] Run `php artisan config:cache`
- [ ] Run `php artisan route:cache`
- [ ] Test authentication flow in production environment

### Production Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.yourdomain.com

SANCTUM_STATEFUL_DOMAINS=yourdomain.com,api.yourdomain.com
SESSION_DOMAIN=.yourdomain.com
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true

CORS_ALLOWED_ORIGINS=https://yourdomain.com
```

### Post-Deployment Steps

1. **Clear and cache configuration**:
   ```bash
   php artisan config:clear
   php artisan config:cache
   ```

2. **Test authentication flow**:
   - Verify CSRF cookie endpoint works
   - Test login/register
   - Verify authenticated requests work

3. **Monitor logs** for authentication errors:
   ```bash
   tail -f storage/logs/laravel.log
   ```

## References

### Official Documentation

- [Laravel Sanctum - SPA Authentication](https://laravel.com/docs/12.x/sanctum#spa-authentication)
- [Laravel Fortify](https://laravel.com/docs/12.x/fortify)
- [Laravel CORS](https://laravel.com/docs/12.x/sanctum#cors-and-cookies)

### External Resources

- [How to authenticate your frontend apps with Laravel Sanctum](https://devloader.hashnode.dev/how-to-authenticate-your-frontend-apps-with-laravel-sanctum)

### Related Documentation

- See `api/.docs/README.md` for general API documentation
- See `api/.docs/caching.md` for caching configuration
- See `api/.docs/reverb.md` for WebSocket configuration
