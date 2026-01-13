<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\User;
use App\Services\BaseService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\Users\UserResource;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use App\Services\Contracts\AuthServiceInterface;
use App\Repositories\Contracts\UserRepositoryInterface;

final class AuthService extends BaseService implements AuthServiceInterface
{
    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->setRepository($userRepository);
    }

    public function register(array $data): array
    {
        /** @var User $user */
        $user = $this->repository->create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        return $this->prepareUserWithToken($user->refresh());
    }

    public function prepareUserWithToken(User $user, ?string $token = null): array
    {
        // Note: JWT functionality removed - using Sanctum instead
        // If JWT is needed, install tymon/jwt-auth package
        return [
            'user'       => new UserResource($user->load('roles', 'permissions')),
            'api_token'  => $token ?? null, // JWT token generation removed
            'token_type' => 'Bearer',
        ];
    }

    public function refresh(): string
    {
        // Note: JWT functionality removed - using Sanctum instead
        // If JWT is needed, install tymon/jwt-auth package
        throw new AuthenticationException('Token refresh not implemented. Using Sanctum for authentication.');
    }

    public function login(array $credentials): array
    {
        throw_unless(Auth::attempt($credentials), AuthenticationException::class, 'Invalid credentials');

        /** @var User $user */
        $user = Auth::user();

        return $this->prepareUserWithToken($user);
    }

    public function me(): Authenticatable
    {
        $user = Auth::user();

        throw_unless($user, AuthenticationException::class, 'User not authenticated');

        return $user;
    }

    public function logout(): bool
    {
        Auth::logout();

        return true;
    }
}
