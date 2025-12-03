<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use App\Actions\Fortify\UpdateUserProfileInformation;
use App\Http\Responses\Fortify\LoginResponse as CustomLoginResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

final class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, CustomLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        $this->configureRateLimiting();
    }

    /**
     * Configure the application's rate limiting for Fortify features.
     *
     * @see https://laravel.com/docs/12.x/routing#rate-limiting
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('login', function (Request $request): Limit {
            // Get email from request, handling missing/empty cases gracefully
            $email = $request->input(Fortify::username(), '');
            $email = mb_trim((string) $email);

            // If email is empty, rate limit by IP only to prevent enumeration attacks
            // Otherwise, rate limit by email|IP combination
            $throttleKey = $email !== ''
                ? Str::transliterate(Str::lower($email)).'|'.$request->ip()
                : $request->ip();

            $limit = app()->isLocal() ? 1000 : 5;

            return Limit::perMinute($limit)->by($throttleKey);
        });

        RateLimiter::for('two-factor', fn (Request $request): Limit => Limit::perMinute(5)->by($request->session()->get('login.id')));
    }
}
