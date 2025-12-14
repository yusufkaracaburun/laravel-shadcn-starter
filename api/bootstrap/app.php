<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Database\QueryException;
use App\Http\Middleware\EnsureTeamIsSet;
use App\Http\Middleware\CacheApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use App\Exceptions\OAuthAccountLinkingException;
use Spatie\Permission\Middleware\RoleMiddleware;
use App\Exceptions\Handlers\HttpExceptionHandler;
use App\Exceptions\Handlers\QueryExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use App\Exceptions\Services\ExceptionLoggerService;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\Handlers\ExceptionHandlerFactory;
use App\Exceptions\Handlers\NotFoundExceptionHandler;
use App\Exceptions\Services\ExceptionResponseBuilder;
use App\Exceptions\Handlers\ExceptionHandlerInterface;
use Spatie\Permission\Middleware\PermissionMiddleware;
use App\Exceptions\Handlers\ValidationExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use App\Exceptions\Handlers\AuthorizationExceptionHandler;
use App\Exceptions\Handlers\AuthenticationExceptionHandler;
use Illuminate\Database\Eloquent\MissingAttributeException;
use App\Exceptions\Handlers\InvalidArgumentExceptionHandler;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Exceptions\Handlers\MethodNotAllowedExceptionHandler;
use App\Exceptions\Handlers\MissingAttributeExceptionHandler;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function (): void {
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

        $middleware->api(
            append: ['throttle:api'],
            prepend: [EnsureFrontendRequestsAreStateful::class],
        );

        $middleware->validateCsrfTokens(except: [
            // API routes are handled by Sanctum
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'team' => EnsureTeamIsSet::class,
            'cache.response' => CacheApiResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReportDuplicates();

        // Initialize exception handling services
        $logger = new ExceptionLoggerService();
        $responseBuilder = new ExceptionResponseBuilder();

        // Initialize and register exception handlers
        $factory = new ExceptionHandlerFactory();
        $factory->register(AuthenticationException::class, new AuthenticationExceptionHandler($logger, $responseBuilder));
        $factory->register(AccessDeniedHttpException::class, new AuthenticationExceptionHandler($logger, $responseBuilder));
        $factory->register(AuthorizationException::class, new AuthorizationExceptionHandler($logger, $responseBuilder));
        $factory->register(ValidationException::class, new ValidationExceptionHandler($logger, $responseBuilder));
        $factory->register(ModelNotFoundException::class, new NotFoundExceptionHandler($logger, $responseBuilder));
        $factory->register(NotFoundHttpException::class, new NotFoundExceptionHandler($logger, $responseBuilder));
        $factory->register(MethodNotAllowedHttpException::class, new MethodNotAllowedExceptionHandler($logger, $responseBuilder));
        $factory->register(InvalidArgumentException::class, new InvalidArgumentExceptionHandler($logger, $responseBuilder));
        $factory->register(MissingAttributeException::class, new MissingAttributeExceptionHandler($logger, $responseBuilder));
        $factory->register(HttpException::class, new HttpExceptionHandler($logger, $responseBuilder));
        $factory->register(QueryException::class, new QueryExceptionHandler($logger, $responseBuilder));

        // Report OAuth account linking exceptions with context
        $exceptions->report(function (OAuthAccountLinkingException $e): void {
            // Log with warning level since these are user-facing errors, not system errors
            Log::warning('OAuth account linking failed', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
        });

        // Render OAuth account linking exceptions properly
        $exceptions->render(fn (OAuthAccountLinkingException $e, $request) => $e->toResponse($request));

        // Render API exceptions using the new handler system
        $exceptions->render(function (Throwable $e, $request) use ($factory): ?\Illuminate\Http\JsonResponse {
            // Only handle API requests
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null;
            }

            $handler = $factory->getHandler($e);
            if ($handler instanceof ExceptionHandlerInterface) {
                return $handler->handle($e, $request);
            }

            return null;
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
