<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Middleware\EnsureTeamIsSet;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\OAuthAccountLinkingException;
use Spatie\Permission\Middleware\RoleMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('webhooks')
                ->name('webhooks.')
                ->group(base_path('routes/webhooks.php'));

        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->api(prepend: [
            HandleCors::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'auth/login-link',
        ]);

        $middleware->trustProxies(at: '*');

        // Register Spatie Permission middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'team' => EnsureTeamIsSet::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Report OAuth account linking exceptions with context
        $exceptions->report(function (OAuthAccountLinkingException $e) {
            // Log with warning level since these are user-facing errors, not system errors
            Log::warning('OAuth account linking failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        });

        // Render OAuth account linking exceptions properly
        $exceptions->render(function (OAuthAccountLinkingException $e, $request) {
            return $e->toResponse($request);
        });

        // Ignore expected exceptions from being reported (they're user-facing, not system errors)
        $exceptions->dontReport([
            AuthenticationException::class,
            AuthorizationException::class,
            ModelNotFoundException::class,
            ValidationException::class,
            HttpException::class,
            NotFoundHttpException::class,
        ]);
    })->create();
