<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\User;
use App\Enums\UserStatus;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Users\UserResource;
use App\Http\Resources\Users\UserCollection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

interface UserServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
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
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getVerifiedUsers(): AnonymousResourceCollection;

    /**
     * Get all active users.
     *
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getActiveUsers(): AnonymousResourceCollection;

    /**
     * Get users by status.
     *
     * @return AnonymousResourceCollection<int, UserResource>
     */
    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): AnonymousResourceCollection;
}
