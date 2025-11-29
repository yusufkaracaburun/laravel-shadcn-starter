# Caching System Documentation

This document describes the Redis-based caching infrastructure implemented in the Laravel API application.

## Overview

The application uses Redis with taggable caching to provide a scalable, production-grade caching layer. All cache operations use tags for efficient invalidation and team-scoped isolation.

## Configuration

### Environment Variables

Ensure the following environment variables are set in your `.env` file:

```env
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_CACHE_DB=1
```

### Cache Default TTL

The default cache TTL is configurable via `CACHE_DEFAULT_TTL` (default: 600 seconds / 10 minutes).

## Cache Tag Structure

The caching system uses the following tag conventions:

- **Permissions**: `permissions`
- **Team data**: `team:{id}`
- **API responses**: `api:{endpoint}`, `user:{id}`, `team:{id}`

## Cache Helper Classes

### TeamCache

Team-scoped caching with tags `team:{id}`.

```php
use App\Support\Cache\TeamCache;

// Get cached value
$value = TeamCache::get($teamId, 'key', $default);

// Remember with callback
$value = TeamCache::remember($teamId, 'key', fn() => $expensiveOperation(), $ttl);

// Store value
TeamCache::put($teamId, 'key', $value, $ttl);

// Forget specific key
TeamCache::forget($teamId, 'key');

// Flush all team cache
TeamCache::flushTags($teamId);
```

### PermissionCache

Permission caching with tag `permissions`.

```php
use App\Support\Cache\PermissionCache;

// Get cached value
$value = PermissionCache::get('key', $default);

// Remember with callback
$value = PermissionCache::remember('key', fn() => $expensiveOperation(), $ttl);

// Flush all permission cache
PermissionCache::flushTags();
```

### ResponseCache

API response caching with tags `api:{endpoint}`, `user:{id}`, `team:{id}`.

```php
use App\Support\Cache\ResponseCache;

// Remember API response
$response = ResponseCache::remember(
    'api.users.index',
    fn() => $data,
    $ttl,
    $userId,
    $teamId
);

// Flush endpoint cache
ResponseCache::flushTags('api.users.index');

// Flush user cache
ResponseCache::flushUserTags($userId);

// Flush team cache
ResponseCache::flushTeamTags($teamId);
```

## Cache Invalidation Service

The `CacheInvalidationService` provides centralized cache invalidation methods:

```php
use App\Support\Cache\CacheInvalidationService;

// Invalidate team caches
CacheInvalidationService::invalidateTeam($teamId);

// Invalidate user caches
CacheInvalidationService::invalidateUser($userId);

// Invalidate permission caches
CacheInvalidationService::invalidatePermissions();

// Event-based invalidation
CacheInvalidationService::onTeamUpdated($teamId);
CacheInvalidationService::onTeamDeleted($teamId);
CacheInvalidationService::onPermissionsChanged();
CacheInvalidationService::onRoleAssigned($teamId);
CacheInvalidationService::onTeamSwitched($userId, $oldTeamId, $newTeamId);
```

## Response Caching Middleware

The `CacheApiResponse` middleware automatically caches GET requests with appropriate tags.

### Usage

Apply the middleware to routes that should be cached:

```php
Route::middleware(['auth:sanctum', 'cache.response'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
});
```

### Excluded Routes

Routes in the `EXCLUDED_ROUTES` array are never cached. Currently excluded:
- `api.user.current`

## Controller Traits

### UsesCachedResponses

Provides caching methods for controllers:

```php
use App\Http\Controllers\Concerns\UsesCachedResponses;

class MyController extends Controller
{
    use UsesCachedResponses;

    public function index()
    {
        $data = $this->cachedResponse('api.my.index', fn() => $expensiveOperation());
        return ApiResponse::success($data);
    }
}
```

### InvalidatesCachedModels

Provides automatic cache invalidation on CRUD operations:

```php
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

class TeamController extends Controller
{
    use InvalidatesCachedModels;

    public function update(Request $request, Team $team)
    {
        $team->update($validated);
        $this->invalidateAfterUpdate('team', $team->id);
        return ApiResponse::success($team);
    }
}
```

## Automatic Cache Invalidation

The system automatically invalidates caches when:

1. **Team updated/deleted**: All team-related caches are flushed
2. **User updated/deleted**: User and team caches are invalidated
3. **Team switched**: Both old and new team caches are invalidated
4. **Role/permission changes**: Permission cache and Spatie cache are reset

### Model Events

Cache invalidation is handled automatically via model events:

- `User::updated` - Invalidates user and team caches
- `User::deleted` - Invalidates user and team caches
- `Team::updated` - Invalidates team caches
- `Team::deleted` - Invalidates team caches
- `Role::created/updated/deleted` - Invalidates permission caches
- `Permission::created/updated/deleted` - Invalidates permission caches

### Spatie Permission Integration

The system automatically resets Spatie Permission cache when:
- Roles are created, updated, or deleted
- Permissions are created, updated, or deleted
- Roles are assigned/removed from users
- Permissions are assigned/removed from users

## Query Caching Examples

### Caching Team Members

```php
use App\Support\Cache\TeamCache;

$members = TeamCache::remember(
    $teamId,
    'members',
    fn() => User::whereHas('teams', fn($q) => $q->where('teams.id', $teamId))->get()
);
```

### Caching User Permissions

```php
use App\Support\Cache\PermissionCache;

$permissions = PermissionCache::remember(
    "user:{$userId}:permissions",
    fn() => $user->getAllPermissions()
);
```

## Deployment Cache Optimization

Run the deployment script before deploying to production:

```bash
./scripts/deploy.sh
```

This script:
1. Clears all caches
2. Runs `php artisan optimize`
3. Caches configuration, routes, views, and events
4. Rebuilds optimized autoloader

## Best Practices

1. **Always use tags**: Never cache without tags to ensure proper invalidation
2. **Set appropriate TTLs**: Use shorter TTLs for frequently changing data
3. **Invalidate on write**: Always invalidate relevant caches when data changes
4. **Use helper classes**: Prefer cache helper classes over direct Cache facade usage
5. **Test invalidation**: Ensure cache invalidation works correctly in tests

## Troubleshooting

### Cache not invalidating

- Check that tags are being used correctly
- Verify Redis connection is working
- Ensure cache invalidation is called after model updates

### Cache not working

- Verify Redis is running and accessible
- Check `CACHE_STORE` environment variable
- Ensure Redis connection configuration is correct

### Performance issues

- Monitor Redis memory usage
- Consider increasing TTL for stable data
- Use cache tags efficiently to avoid over-invalidation

