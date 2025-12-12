<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Team;
use App\Models\User;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use App\Models\InvoiceItem;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use App\Observers\RoleObserver;
use App\Observers\TeamObserver;
use App\Observers\UserObserver;
use App\Observers\ContactObserver;
use App\Observers\InvoiceObserver;
use App\Observers\PaymentObserver;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Observers\CustomerObserver;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Vite;
use App\Observers\PermissionObserver;
use App\Observers\InvoiceItemObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Resources\Json\JsonResource;

final class AppServiceProvider extends ServiceProvider
{
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
        $this->configureCommands();
        $this->configureDates();
        $this->configureModels();
        $this->configureUrl();
        $this->configureVite();
        $this->configureRateLimiting();
        $this->registerObservers();
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
     * @see https://laravel.com/docs/12.x/routing#rate-limiting
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login-link', fn (Request $request): Limit => Limit::perMinute((int) config('login-link.rate_limit_attempts', 1))->by($request->email ?? $request->ip()));

        RateLimiter::for('api', fn (Request $request): Limit => Limit::perMinute(60)->by($request->user()?->id ?: $request->ip()));
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
        Customer::observe(CustomerObserver::class);
        Contact::observe(ContactObserver::class);
        Payment::observe(PaymentObserver::class);
        Invoice::observe(InvoiceObserver::class);
        InvoiceItem::observe(InvoiceItemObserver::class);
    }
}
