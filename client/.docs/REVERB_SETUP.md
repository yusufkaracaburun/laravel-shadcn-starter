# Laravel Reverb Setup Guide

This guide explains how to configure Reverb when your API and client have separate `.env` files in different directories.

## Directory Structure

```
skeleton-1/
├── api/
│   └── .env          # API environment variables
└── client/
    └── .env          # Client environment variables
```

## Configuration Overview

The API and client need to share **some** values (public credentials), but the API also has **private** credentials that should never be in the client `.env`.

### Values That Must Match

These values must be **identical** in both `.env` files:

- `REVERB_APP_KEY` (public key - safe to share)
- `REVERB_HOST` (where the Reverb server is accessible)
- `REVERB_PORT` (port the Reverb server listens on)
- `REVERB_SCHEME` (http or https)

### API-Only Values

These values should **only** be in the API `.env`:

- `REVERB_APP_SECRET` (private - never share with client)
- `REVERB_APP_ID` (server-side only)

## API `.env` Configuration

Add these to `api/.env`:

```env
# Reverb Configuration
BROADCAST_CONNECTION=reverb

# Reverb Application Credentials (shared with client)
REVERB_APP_KEY=your-app-key-here
REVERB_HOST=localhost
REVERB_PORT=9999
REVERB_SCHEME=http

# Reverb Server Credentials (API only - DO NOT share with client)
REVERB_APP_SECRET=your-app-secret-here
REVERB_APP_ID=your-app-id-here
```

## Client `.env` Configuration

Add these to `client/.env`:

```env
# Reverb Configuration
VITE_REVERB_ENABLED=true

# Reverb Connection (must match API values)
VITE_REVERB_APP_KEY=your-app-key-here          # Same as REVERB_APP_KEY in api/.env
VITE_REVERB_HOST=localhost                      # Same as REVERB_HOST in api/.env
VITE_REVERB_PORT=9999                           # Same as REVERB_PORT in api/.env
VITE_REVERB_SCHEME=http                         # Same as REVERB_SCHEME in api/.env
```

## Quick Setup Steps

1. **Generate Reverb credentials** (if not already done):

   ```bash
   cd api
   php artisan reverb:install
   ```

   This will generate and display the credentials.

2. **Copy the public values to client `.env`**:
   - Copy `REVERB_APP_KEY` → `VITE_REVERB_APP_KEY`
   - Copy `REVERB_HOST` → `VITE_REVERB_HOST`
   - Copy `REVERB_PORT` → `VITE_REVERB_PORT`
   - Copy `REVERB_SCHEME` → `VITE_REVERB_SCHEME`

3. **Keep secrets in API only**:
   - `REVERB_APP_SECRET` stays in `api/.env` only
   - `REVERB_APP_ID` stays in `api/.env` only

## Example: Complete Configuration

### `api/.env`

```env
BROADCAST_CONNECTION=reverb
REVERB_APP_KEY=p4ekv8fs13h3d4ec2iy1
REVERB_APP_SECRET=cys1za6lddfukukfrjzn
REVERB_APP_ID=358622
REVERB_HOST=localhost
REVERB_PORT=9999
REVERB_SCHEME=http
```

### `client/.env`

```env
VITE_REVERB_ENABLED=true
VITE_REVERB_APP_KEY=p4ekv8fs13h3d4ec2iy1
VITE_REVERB_HOST=localhost
VITE_REVERB_PORT=9999
VITE_REVERB_SCHEME=http
```

## How It Works

1. **API Side** (`api/.env`):
   - Uses `REVERB_APP_KEY`, `REVERB_APP_SECRET`, and `REVERB_APP_ID` to authenticate the Reverb server
   - The server runs on `REVERB_HOST:REVERB_PORT`
   - Validates client connections using the shared `REVERB_APP_KEY`

2. **Client Side** (`client/.env`):
   - Uses `VITE_REVERB_APP_KEY` to connect to the Reverb server
   - Connects to `VITE_REVERB_HOST:VITE_REVERB_PORT`
   - The `VITE_` prefix makes these available in the browser (Vite requirement)

## Security Notes

- ✅ **Safe to share**: `REVERB_APP_KEY`, `REVERB_HOST`, `REVERB_PORT`, `REVERB_SCHEME`
- ❌ **Never share**: `REVERB_APP_SECRET`, `REVERB_APP_ID`
- The client only needs the public key to connect; the secret is only used server-side for authentication

## Troubleshooting

### Connection Issues

If the client can't connect to Reverb:

1. **Check values match**: Ensure `VITE_REVERB_APP_KEY` in client matches `REVERB_APP_KEY` in API
2. **Check host/port**: Ensure `VITE_REVERB_HOST` and `VITE_REVERB_PORT` match where Reverb is running
3. **Check Reverb is running**: Run `php artisan reverb:start` in the API directory
4. **Check CORS/Allowed Origins**:
   - Ensure the client origin is in `REVERB_ALLOWED_ORIGINS` in API `.env`
   - Or configure `allowed_origins => ['*']` in `api/config/reverb.php` for development (not recommended for production)

### Authentication Issues

If private channels don't work:

1. **Check auth endpoint**: The client uses `${VITE_SERVER_API_URL}${VITE_SERVER_API_PREFIX}/broadcasting/auth`
2. **Check credentials**: Ensure `withCredentials: true` is set (already configured in Echo setup)
3. **Check Sanctum**: Ensure your Sanctum configuration allows the client origin

## Production Considerations

For production:

1. **Use HTTPS**: Set `REVERB_SCHEME=https` and `VITE_REVERB_SCHEME=https`
2. **Use domain**: Set `REVERB_HOST=your-domain.com` (not localhost)
3. **Secure secrets**: Never commit `.env` files with real credentials
4. **Environment-specific configs**: Use different values for staging/production
5. **Restrict allowed origins**: Never use `['*']` for `allowed_origins` in production - specify exact origins
