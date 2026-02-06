<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\User;
use App\Enums\UserStatus;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use App\Repositories\Contracts\UserRepositoryInterface;

final class UserRepository extends QueryableRepository implements UserRepositoryInterface
{
    public function getDefaultSorts(): array
    {
        return ['name'];
    }

    public function getAllowedSorts(): array
    {
        return [
            'id',
            'name',
            'email',
            'status',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedFields(): array
    {
        return [
            'id',
            'name',
            'email',
            'status',
            'email_verified_at',
            'current_team_id',
            'created_at',
            'updated_at',
        ];
    }

    public function getAllowedIncludes(): array
    {
        return ['roles'];
    }

    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::partial('name'),
            AllowedFilter::partial('email'),
            AllowedFilter::exact('status'),
            AllowedFilter::scope('created_at'),
        ];
    }

    public function findOrFail(int $id, array $columns = ['*']): User
    {
        return User::query()->findOrFail($id, $columns);
    }

    /**
     * Find user for show endpoint with relationships loaded.
     */
    public function findForShow(User $user): User
    {
        return $this->loadRelationships($user);
    }

    /**
     * Create a new user and load relationships.
     */
    public function createWithRelationships(array $data): User
    {
        /** @var User $user */
        $user = parent::create($data);

        return $this->loadRelationships($user);
    }

    /**
     * Update user and load relationships.
     */
    public function updateWithRelationships(User $user, array $data): User
    {
        /** @var User $updated */
        $updated = parent::update($user, $data);

        return $this->loadRelationships($updated);
    }

    public function getCurrentUser(User $user): User
    {
        return $this->findForShow($user);
    }

    public function getVerifiedUsers(): Collection
    {
        return User::query()
            ->whereNotNull('email_verified_at')
            ->get();
    }

    public function getActiveUsers(): Collection
    {
        return $this->getUsersByStatus(UserStatus::ACTIVE);
    }

    public function getUsersByStatus(UserStatus|string $status = UserStatus::ACTIVE): Collection
    {
        $statusEnum = $status instanceof UserStatus ? $status : UserStatus::from($status);

        return User::query()
            ->where('status', $statusEnum->value)
            ->get();
    }

    protected function model(): string
    {
        return User::class;
    }

    /**
     * Standardize relationship loading in one place.
     */
    private function loadRelationships(Model $user): User
    {
        /** @var User $user */
        return $user->load('roles');
    }
}
