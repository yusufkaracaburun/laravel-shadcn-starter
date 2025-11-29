<?php

declare(strict_types=1);

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Knuckles\Scribe\Extracting\ExtractedEndpointData;
use Knuckles\Scribe\Scribe;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\Sanctum;
use Symfony\Component\HttpFoundation\Request as HttpFoundationRequest;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        $this->configureCommands();
        $this->configureDates();
        $this->configureModels();
        $this->configureUrl();
        $this->configureVite();
        $this->configureRateLimiting();
        $this->configureScribeDocumentation();
    }

    /**
     * Configure the application's commands.
     */
    private function configureCommands(): void
    {
        DB::prohibitDestructiveCommands(App::isProduction());
    }

    /**
     * It's recommended to use CarbonImmutable as it's immutable and thread-safe to avoid issues with mutability.
     *
     * @see https://dyrynda.com.au/blog/laravel-immutable-dates
     */
    private function configureDates(): void
    {
        Date::use(CarbonImmutable::class);
    }

    /**
     * Configure the application's models.
     * This is optional, but it's recommended to enable strict mode and disable mass assignment.
     *
     * @see https://laravel.com/docs/eloquent#configuring-eloquent-strictness
     */
    private function configureModels(): void
    {
        Model::shouldBeStrict();

        Model::unguard();
    }

    /**
     * Configure the application's URL.
     * This is optional, but it's recommended to force HTTPS in production.
     *
     * @see https://laravel.com/docs/octane#serving-your-application-via-https
     */
    private function configureUrl(): void
    {
        URL::forceHttps(App::isProduction());
    }

    /**
     * Configure the application's Vite loading strategy.
     * This is optional, but it's recommended to use aggressive prefetching so the UI feels snappy.
     */
    private function configureVite(): void
    {
        Vite::useAggressivePrefetching();
    }

    /**
     * Configure the application's rate limiting.
     *
     * @see https://laravel.com/docs/11.x/routing#rate-limiting
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', fn (Request $request) => $request->email ? Limit::perMinute(5)->by($request->email) : Limit::perMinute(5)->by($request->ip()));
        
        RateLimiter::for('login-link', fn (Request $request) => Limit::perMinute((int) config('login-link.rate_limit_attempts', 1))->by($request->email ?? $request->ip()));
    }

    /**
     * Configure the application's Scribe documentation.
     *
     * @see https://scribe.knuckles.wtf/laravel/
     */
    private function configureScribeDocumentation(): void
    {
        if (class_exists(Scribe::class)) {
            Scribe::beforeResponseCall(function (HttpFoundationRequest $request, ExtractedEndpointData $endpointData): void {
                $user = User::query()->first();
                if ($user) {
                    Sanctum::actingAs($user, ['*']);
                }
            });
        }
    }
}

