<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;
use App\Http\Resources\Users\UserResource;
use Illuminate\Contracts\Auth\Authenticatable;

interface AuthServiceInterface
{
    public function register(array $data): array;

    public function login(array $credentials): array;

    public function me(): Authenticatable;

    public function refresh(): string;

    public function logout(): bool;

    public function prepareUserWithToken(User $user, ?string $token = null): array;

    public function getCurrentUser(User $user): UserResource;
}
