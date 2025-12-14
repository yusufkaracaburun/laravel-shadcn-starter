<?php

declare(strict_types=1);

namespace App\Repositories\Concretes;

use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Repositories\QueryableRepository;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\QueryBuilderRequest;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectRepository extends QueryableRepository implements ProjectRepositoryInterface
{
    /**
     * Get paginated projects with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator
    {
        // Start with base query
        $query = Project::query();

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->where('team_id', $teamId);
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
     * Find a project by ID with relationships loaded.
     */
    public function findById(int $projectId, ?int $teamId = null): Project
    {
        $query = Project::query()->where('id', $projectId);

        // Apply team filtering if teamId is provided
        if ($teamId !== null) {
            $query->where('team_id', $teamId);
        }

        return $query->firstOrFail();
    }

    /**
     * Create a new project with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped project creation
     */
    public function createProject(array $data, ?int $teamId = null): Project
    {
        // Set team_id if provided
        if ($teamId !== null) {
            $data['team_id'] = $teamId;
        }

        return Project::query()->create($data);
    }

    /**
     * Update a project by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateProject(Project $project, array $data, ?int $teamId = null): Project
    {
        // Set team_id if provided
        if ($teamId !== null) {
            $data['team_id'] = $teamId;
        }

        $project->update($data);
        $project->refresh();

        return $project;
    }

    /**
     * Delete a project by model instance.
     */
    public function deleteProject(Project $project): bool
    {
        return $project->delete();
    }

    /**
     * Get allowed filters for this repository.
     */
    public function getAllowedFilters(): array
    {
        return [
            AllowedFilter::exact('id'),
            AllowedFilter::exact('name'),
            AllowedFilter::exact('status'),
            AllowedFilter::exact('priority'),
            AllowedFilter::exact('category'),
            AllowedFilter::exact('team_id'),
            AllowedFilter::scope('created_at'),
        ];
    }

    /**
     * Get allowed sorts for this repository.
     */
    public function getAllowedSorts(): array
    {
        return ['id', 'name', 'status', 'priority', 'category', 'start_date', 'end_date', 'progress', 'created_at'];
    }

    /**
     * Get allowed includes for this repository.
     */
    public function getAllowedIncludes(): array
    {
        return ['team'];
    }

    /**
     * Model binding.
     */
    protected function model(): string
    {
        return Project::class;
    }
}
