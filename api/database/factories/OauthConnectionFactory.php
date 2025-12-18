<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\OauthConnection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OauthConnection>
 */
final class OauthConnectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'provider'      => 'github',
            'provider_id'   => (string) fake()->randomNumber(8),
            'data'          => ['name' => fake()->name()],
            'token'         => fake()->uuid(),
            'refresh_token' => fake()->uuid(),
            'expires_at'    => now()->addHour(),
        ];
    }
}
