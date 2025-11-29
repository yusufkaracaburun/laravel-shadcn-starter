<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Team;
use Carbon\CarbonImmutable;
use App\Policies\TeamPolicy;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Laravel\Sanctum\Sanctum;
use App\Policies\StudentPolicy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Team::class => TeamPolicy::class,
        // Add Student model when available:
        // Student::class => StudentPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        /**
         * This is optional, but it's recommended to register Telescope in local environment.
         * You are free to remove this if you don't want to use Telescope.
         * Remove the migration files if you don't want to use Telescope.
         *
         * @see https://laravel.com/docs/telescope
         */
        if ($this->app->environment('local') && class_exists(\Laravel\Telescope\TelescopeServiceProvider::class)) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);

        $this->registerPolicies();

        $this->configureCommands();
        $this->configureDates();
        $this->configureModels();
        $this->configureUrl();
        $this->configureVite();
        $this->configureRateLimiting();
    }

    /**
     * Register the application's policies.
     */
    private function registerPolicies(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
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

}
