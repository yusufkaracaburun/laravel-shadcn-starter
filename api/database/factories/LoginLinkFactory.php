<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\LoginLink;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LoginLink>
 */
final class LoginLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'token' => Str::random(64),
            'expires_at' => now()->addMinutes(15),
            'used_at' => null,
        ];
    }
}
