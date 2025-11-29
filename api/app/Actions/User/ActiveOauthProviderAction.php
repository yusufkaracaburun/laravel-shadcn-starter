<?php

declare(strict_types=1);

namespace App\Actions\User;

use Illuminate\Support\Facades\Config;

final class ActiveOauthProviderAction
{
    /**
     * @var array<string, array<int, string>>
     */
    private const PROVIDER_CREDENTIALS = [
        'github' => ['GITHUB_CLIENT_ID', 'GITHUB_CLIENT_SECRET'],
        'google' => ['GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET'],
        'x' => ['X_CLIENT_ID', 'X_CLIENT_SECRET'],
        'gitlab' => ['GITLAB_CLIENT_ID', 'GITLAB_CLIENT_SECRET'],
        'bitbucket' => ['BITBUCKET_CLIENT_ID', 'BITBUCKET_CLIENT_SECRET'],
        'discord' => ['DISCORD_CLIENT_ID', 'DISCORD_CLIENT_SECRET'],
    ];

    /**
     * Get all active OAuth providers.
     *
     * @return array<int, array{slug: string, active: bool, icon: string}>
     */
    public function handle(): array
    {
        /** @var array<int, array{slug: string, active: bool, icon: string}> $providers */
        $providers = Config::get('oauth.providers', []);

        return array_values(array_filter($providers, function (array $provider): bool {
            if (! $provider['active']) {
                return false;
            }

            if (app()->environment('testing')) {
                return true;
            }

            return $this->hasCredentials($provider['slug']);
        }));
    }

    private function hasCredentials(string $provider): bool
    {
        if (! array_key_exists($provider, self::PROVIDER_CREDENTIALS)) {
            return true;
        }

        foreach (self::PROVIDER_CREDENTIALS[$provider] as $key) {
            if (blank(env($key))) {
                return false;
            }
        }

        return true;
    }
}

