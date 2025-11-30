<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use Carbon\CarbonImmutable;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Http\Request;
use App\Observers\RoleObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use App\Observers\PermissionObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Illuminate\Support\Facades\RateLimiter;

final class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    private array $policies = [
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
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
        $this->registerPolicies();
        $this->configureSuperAdmin();

        $this->configureCommands();
        $this->configureDates();
        $this->configureModels();
        $this->configureUrl();
        $this->configureVite();
        $this->configureRateLimiting();
        $this->registerObservers();
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
     * Configure super admin to have all permissions.
     * Implicitly grant "Super Admin" role all permissions via Gate::before.
     * This works in the app by using gate-related functions like auth()->user->can() and @can().
     */
    private function configureSuperAdmin(): void
    {
        Gate::before(function ($user, $ability): ?true {
            // Check for super-admin role with team context cleared (global role)
            if (! $user) {
                return null;
            }

            $permissionRegistrar = app(PermissionRegistrar::class);
            $originalTeamId = $permissionRegistrar->getPermissionsTeamId();
            $permissionRegistrar->setPermissionsTeamId(null); // Check global roles

            $isSuperAdmin = $user->hasRole('super-admin');

            // Restore original team context
            $permissionRegistrar->setPermissionsTeamId($originalTeamId);

            return $isSuperAdmin ? true : null;
        });
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

        // Set Carbon locale to match application locale
        CarbonImmutable::setLocale(config('app.locale'));

        // Add a macro to make format() use translated format by default
        // This allows format() to automatically use Dutch day/month names
        CarbonImmutable::macro('formatLocalized', function (string $format): string {
            /** @var CarbonImmutable $this */
            return $this->translatedFormat($format);
        });
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
        RateLimiter::for('login-link', fn (Request $request): Limit => Limit::perMinute((int) config('login-link.rate_limit_attempts', 1))->by($request->email ?? $request->ip()));
    }

    /**
     * Register model observers.
     */
    private function registerObservers(): void
    {
        User::observe(UserObserver::class);
        Team::observe(TeamObserver::class);
        Role::observe(RoleObserver::class);
        Permission::observe(PermissionObserver::class);
    }
}
