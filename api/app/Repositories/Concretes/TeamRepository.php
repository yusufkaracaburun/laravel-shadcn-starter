<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Team;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamRepository extends QueryableRepository implements TeamRepositoryInterface
{
    /**
     * Get paginated teams with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     * Teams are scoped to the authenticated user (teams they own or belong to).
     */
    public function getPaginated(int $perPage, ?int $userId = null): LengthAwarePaginator
    {
        // Start with base query
        $query = Team::query();

        // Apply user filtering - get teams user owns or belongs to
        if ($userId !== null) {
            $ownedTeamIds = Team::query()->where('user_id', $userId)->pluck('id');
            $memberTeamIds = Team::query()
                ->whereHas('users', fn ($q) => $q->where('users.id', $userId))
                ->pluck('id');
            $allTeamIds = $ownedTeamIds->merge($memberTeamIds)->unique();
            $query->whereIn('id', $allTeamIds);
        }

        // Get the original request and create a modified version with our per_page
        $originalRequest = request();
        $queryParams = $originalRequest->query->all();
        $queryParams['per_page'] = (string) $perPage;
        $queryParams['page'] = (string) ($originalRequest->input('page', 1));

        // Build URL with query string
        $url = $originalRequest->url();
        $queryString = http_build_query($queryParams);
        $fullUrl = $queryString !== '' && $queryString !== '0' ? $url.'?'.$queryString : $url;

        // Create a new request with modified query parameters
        $modifiedRequest = Request::create(
            $fullUrl,
            $originalRequest->method(),
            $originalRequest->request->all(),
            $originalRequest->cookies->all(),
            $originalRequest->files->all(),
            $originalRequest->server->all(),
            $originalRequest->getContent()
        );

        // Build query with QueryBuilder for filtering, sorting, and includes
        $queryRequest = QueryBuilderRequest::fromRequest($modifiedRequest);
        $queryBuilder = QueryBuilder::for($query, $queryRequest)
            ->allowedFilters($this->getAllowedFilters())
            ->allowedSorts($this->getAllowedSorts())
            ->allowedIncludes($this->getAllowedIncludes());

        // Get the underlying Eloquent builder after QueryBuilder has applied filters/sorts/includes
        // Then paginate directly with our per_page value
        $eloquentBuilder = $queryBuilder->getEloquentBuilder();

        // Get the page number from the modified request
        $page = (int) $modifiedRequest->input('page', 1);

        // Paginate directly on the Eloquent builder
        return $eloquentBuilder->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Find a team by ID with user access check.
     * Only returns team if user owns it or belongs to it.
     */
    public function findById(int $teamId, ?int $userId = null): Team
    {
        $query = Team::query()->where('id', $teamId);

        // Apply user access check - user must own or belong to the team
        if ($userId !== null) {
            $query->where(function ($q) use ($userId): void {
                $q->where('user_id', $userId)
                    ->orWhereHas('users', fn ($subQ) => $subQ->where('users.id', $userId));
            });
        }

        return $query->firstOrFail();
    }

    /**
     * Create a new team for a user.
     *
     * @param  array<string, mixed>  $data
     * @param  int  $userId  User ID for team creation
     */
    public function createTeam(array $data, int $userId): Team
    {
        // Set user_id
        $data['user_id'] = $userId;

        // Default personal_team to false if not provided
        if (! isset($data['personal_team'])) {
            $data['personal_team'] = false;
        }

        return Team::query()->create($data);
    }

    /**
     * Update a team by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateTeam(Team $team, array $data): Team
    {
        $team->update($data);
        $team->refresh();

        return $team;
    }

    /**
     * Delete a team by model instance.
     */
    public function deleteTeam(Team $team): bool
    {
        return $team->delete();
    }

    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('personal_team'),
            AllowedFilter::exact('user_id'),
            AllowedFilter::scope('created_at'),
        ];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return ['id', 'name', 'personal_team', 'created_at'];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return ['owner', 'users', 'teamInvitations'];
    }

    /**
     * Get allowed fields for this repository.
     */
    public function getAllowedFields(): array
    {
        return [];
    }

    /**
     * Get default sorts for this repository.
     */
    public function getDefaultSorts(): array
    {
        return [];
    }

    /**
     * Get all teams for a specific user.
     */
    public function getTeamsForUser(int $userId): Collection
    {
        return $this->model
            ->whereHas('users', fn ($query) => $query->where('users.id', $userId))
            ->orWhere('user_id', $userId)
            ->get();
    }

    /**
     * Get personal team for a user.
     */
    public function getPersonalTeamForUser(int $userId): ?Team
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('personal_team', true)
            ->first();
    }

    /**
     * Get teams owned by a user.
     */
    public function getOwnedTeamsForUser(int $userId): Collection
    {
        return $this->model
            ->where('user_id', $userId)
            ->get();
    }

    /**
     * Model binding.
     */
    protected function model(): string
    {
        return Team::class;
    }
}
