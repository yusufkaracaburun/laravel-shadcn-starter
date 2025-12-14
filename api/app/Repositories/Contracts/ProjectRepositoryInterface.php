<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Project;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface ProjectRepositoryInterface extends QueryableRepositoryInterface
{
    /**
     * Get paginated projects with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     *
     * @return LengthAwarePaginator<Project>
     */
    public function getPaginated(int $perPage, ?int $teamId = null): LengthAwarePaginator;

    /**
     * Find a project by ID with relationships loaded.
     */
    public function findById(int $projectId, ?int $teamId = null): Project;

    /**
     * Create a new project with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped project creation
     */
    public function createProject(array $data, ?int $teamId = null): Project;

    /**
     * Update a project by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateProject(Project $project, array $data, ?int $teamId = null): Project;

    /**
     * Delete a project by model instance.
     */
    public function deleteProject(Project $project): bool;
}
