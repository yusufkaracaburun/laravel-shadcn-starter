<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\User;
use App\Support\Cache\TeamCache;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * User Repository
 *
 * Note: This class is not marked as final to allow mocking in unit tests.
 */
class UserRepository
{
    /**
     * Get paginated users, optionally filtered by team.
     *
     * @return LengthAwarePaginator<User>
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator
    {
        if ($teamId === null) {
            return User::query()->paginate($perPage);
        }

        return User::query()
            ->whereHas('teams', fn ($query) => $query->where('teams.id', $teamId))
            ->orWhereHas('ownedTeams', fn ($query) => $query->where('id', $teamId))
            ->paginate($perPage);
    }

    /**
     * Find a user by ID with relationships loaded.
     */
    public function findById(int $userId, ?int $teamId = null): User
    {
        $user = User::query()->findOrFail($userId);

        if ($teamId === null) {
            $user->load(['teams', 'currentTeam', 'ownedTeams']);

            return $user;
        }

        return TeamCache::remember(
            $teamId,
            "users:{$userId}",
            fn () => $user->load(['teams', 'currentTeam', 'ownedTeams'])
        );
    }

    /**
     * Create a new user.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User
    {
        // Handle profile photo upload if present
        if (isset($data['profile_photo']) && $data['profile_photo'] instanceof UploadedFile) {
            $photo = $data['profile_photo'];
            $path = $photo->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
            unset($data['profile_photo']);
        }

        $user = User::query()->create($data);
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        return $user;
    }

    /**
     * Update a user.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);
        $user->refresh();
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        return $user;
    }

    /**
     * Delete a user.
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Get the current authenticated user with relationships loaded.
     */
    public function getCurrentUser(User $user): User
    {
        $user->refresh();
        $user->load(['teams', 'currentTeam', 'ownedTeams']);

        return $user;
    }
}
