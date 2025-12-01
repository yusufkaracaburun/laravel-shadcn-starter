# Sanctum Cross-Domain Authentication Setup

This guide explains how to configure Laravel Sanctum for cross-domain authentication between your frontend and backend applications.

## Overview

- **Frontend**: Vue.js application (localhost:5173 in development)
- **Backend**: Laravel API (127.0.0.1:8000 in development)
- **Authentication**: Laravel Sanctum with cookie-based session authentication

## Environment Variables Configuration

### Backend (`api/.env`)

#### Development Configuration

Add or update these variables in `api/.env`:

```env
# Sanctum Configuration - Include both frontend and backend domains
SANCTUM_STATEFUL_DOMAINS=localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000

# Session Configuration
# For localhost development with mixed origins (localhost:5173 and 127.0.0.1:8000),
# set SESSION_DOMAIN to empty (null) to allow cookies on both localhost and 127.0.0.1
SESSION_DOMAIN=
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=false

# Application URLs
APP_URL=http://127.0.0.1:8000
FRONTEND_URL=http://localhost:5173

# CORS Configuration (optional, already has defaults)
CORS_ALLOWED_ORIGINS=http://localhost:5173,http://127.0.0.1:5173
```

#### Production Configuration

For production, update with your actual domains:

```env
# Sanctum Configuration (include both frontend and backend domains)
SANCTUM_STATEFUL_DOMAINS=your-frontend-domain.com,your-backend-domain.com

# Session Configuration
SESSION_DOMAIN=.your-domain.com
SESSION_SAME_SITE=lax
SESSION_SECURE_COOKIE=true

# Application URLs
APP_URL=https://your-backend-domain.com
FRONTEND_URL=https://your-frontend-domain.com

# CORS Configuration
CORS_ALLOWED_ORIGINS=https://your-frontend-domain.com
```

### Frontend (`client/.env`)

#### Development Configuration

Add or update these variables in `client/.env`:

```env
VITE_SERVER_API_URL=http://127.0.0.1:8000
VITE_SERVER_API_PREFIX=/api
```

#### Production Configuration

For production:

```env
VITE_SERVER_API_URL=https://your-backend-domain.com
VITE_SERVER_API_PREFIX=/api
```

## Key Configuration Points

### 1. SANCTUM_STATEFUL_DOMAINS

**Critical**: Must include **both** frontend AND backend domains/ports.

- **Development**: `localhost:5173,127.0.0.1:5173,localhost:8000,127.0.0.1:8000`
- **Production**: Include both your frontend and backend domains

This tells Sanctum which domains should receive stateful authentication cookies.

### 2. SESSION_DOMAIN

- **Development**: 
  - **Empty/null** (set `SESSION_DOMAIN=`) if backend is on `127.0.0.1:8000` and frontend on `localhost:5173` (mixed origins)
  - `localhost` only if both backend and frontend use `localhost` (same origin)
- **Production**: Set to your domain (e.g., `.yourdomain.com` for subdomain support)

**Important**: For mixed origins (`localhost:5173` → `127.0.0.1:8000`), you MUST set `SESSION_DOMAIN=` (empty) or cookies won't be set due to browser security restrictions.

### 3. SESSION_SAME_SITE

- **Development**: `lax` (default, works for localhost)
- **Production**: `lax` or `none` (if using different domains, may need `none` with `secure=true`)

### 4. CORS Configuration

The CORS configuration in `api/config/cors.php` already includes:
- Paths: `api/*` and `sanctum/csrf-cookie` ✅
- `supports_credentials: true` ✅
- Default origins for localhost ✅

For production, set `CORS_ALLOWED_ORIGINS` in your `.env` file.

## Frontend Axios Configuration

The frontend axios configuration (`client/src/composables/use-axios.ts`) has been updated to include:

- `Accept: application/json` header (required by Laravel)
- `X-Requested-With: XMLHttpRequest` header (required by Laravel)
- `withCredentials: true` (required for cookies)
- Proper base URL configuration using `VITE_SERVER_API_URL`

## Authentication Flow

The authentication flow is already implemented in `client/src/composables/use-auth.ts`:

1. **First**: GET `/sanctum/csrf-cookie` to receive CSRF cookie
2. **Then**: POST `/login` or `/register` with credentials
3. **Subsequent requests**: Include XSRF-TOKEN header automatically

## Testing

### Development Testing

1. Ensure both servers are running:
   - Backend: `php artisan serve` (runs on 127.0.0.1:8000)
   - Frontend: `npm run dev` (runs on localhost:5173)

2. Test the authentication flow:
   - [ ] Login request succeeds without 419 error
   - [ ] CSRF cookie is received from `/sanctum/csrf-cookie`
   - [ ] XSRF-TOKEN cookie is set and sent in subsequent requests
   - [ ] Register endpoint works
   - [ ] Logout endpoint works
   - [ ] Authenticated API requests work
   - [ ] Works with both `localhost:5173` and `127.0.0.1:5173`

### Production Testing

- [ ] All development tests pass
- [ ] HTTPS is properly configured
- [ ] Cookies work across domains (if applicable)
- [ ] CORS is properly configured
- [ ] Config cache is cleared after deployment: `php artisan config:cache`

## Troubleshooting

### 419 CSRF Token Mismatch

1. **Check SANCTUM_STATEFUL_DOMAINS**: Must include both frontend and backend domains
2. **Check SESSION_DOMAIN**: Should be `localhost` for development
3. **Check CORS**: Ensure frontend origin is in `CORS_ALLOWED_ORIGINS`
4. **Check headers**: Ensure `Accept: application/json` and `X-Requested-With: XMLHttpRequest` are set
5. **Clear config cache**: Run `php artisan config:clear` (dev) or `php artisan config:cache` (prod)

### Cookies Not Being Set

1. **Check SESSION_DOMAIN**: For localhost, use `localhost` (not null/empty)
2. **Check SESSION_SECURE_COOKIE**: Should be `false` for HTTP in development
3. **Check withCredentials**: Must be `true` on all axios requests
4. **Check browser console**: Look for cookie-related errors

### CORS Errors

1. **Check CORS_ALLOWED_ORIGINS**: Must include your frontend domain
2. **Check CORS paths**: Must include `api/*` and `sanctum/csrf-cookie`
3. **Check supports_credentials**: Must be `true` in CORS config

## Production Considerations

1. **HTTPS Required**: In production, ensure:
   - `SESSION_SECURE_COOKIE=true`
   - `APP_URL` uses `https://`
   - Frontend URL uses `https://`

2. **Domain Configuration**:
   - If frontend and backend are on same domain: `SESSION_DOMAIN=.yourdomain.com`
   - If different domains: May need `SESSION_SAME_SITE=none` with `SESSION_SECURE_COOKIE=true`

3. **CORS**: Set `CORS_ALLOWED_ORIGINS` to your production frontend domain(s)

4. **Cache**: After config changes, run `php artisan config:cache` in production

## References

- [Laravel Sanctum Documentation](https://laravel.com/docs/12.x/sanctum#spa-authentication)
- [How to authenticate your frontend apps with Laravel Sanctum](https://devloader.hashnode.dev/how-to-authenticate-your-frontend-apps-with-laravel-sanctum)

