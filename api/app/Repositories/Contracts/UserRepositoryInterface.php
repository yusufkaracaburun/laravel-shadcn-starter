<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface UserRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get paginated users with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     *
     * @return LengthAwarePaginator<User>
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator;

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): User;

    /**
     * Update a user by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped role assignment
     */
    public function updateUser(User $user, array $data, ?int $teamId = null): User;

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
    public function createUser(array $data, ?int $teamId = null): User;

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): User;
}
