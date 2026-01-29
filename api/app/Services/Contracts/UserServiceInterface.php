<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;
use App\Enums\UserStatus;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserCollection;

interface UserServiceInterface extends BaseServiceInterface
{
    public function getPaginated(Request $request): UserCollection;

    public function show(User $user): UserResource;

    /**
     * Create new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function createUser(array $data): UserResource;

    /**
     * Update user.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateUser(User $user, array $data): UserResource;

    /**
     * Delete user.
     */
    public function deleteUser(User $user): bool;

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): UserResource;

    /**
     * Get all users.
     */
    public function getAll(): UserCollection;

    /**
     * Get all verified users.
     */
    public function getVerifiedUsers(): UserCollection;

    /**
     * Get all active users.
     */
    public function getActiveUsers(): UserCollection;

    /**
     * Get users by status.
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): UserCollection;
}
