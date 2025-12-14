<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Enums\UserStatus;
use App\Models\User;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Database\Eloquent\Collection;
use App\Services\BaseServiceInterface;

interface UserServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     *
     * @return UserCollection
     */
    public function getPaginated(int $perPage, ?int $teamId = null): UserCollection;

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): UserResource;

    /**
     * Update a user by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function updateUser(User $user, array $data, ?int $teamId = null): UserResource;

    /**
     * Delete a user by model instance.
     */
    public function deleteUser(User $user): bool;

    /**
     * Create a new user with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function createUser(array $data, ?int $teamId = null): UserResource;

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): UserResource;

    /**
     * Get all verified users.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<int, UserResource>
     */
    public function getVerifiedUsers(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    /**
     * Get all active users.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<int, UserResource>
     */
    public function getActiveUsers(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection;

    /**
     * Get users by status.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection<int, UserResource>
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): \Illuminate\Http\Resources\Json\AnonymousResourceCollection;
}
