<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Project;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\Projects\ProjectCollection;

interface ProjectServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated projects with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): ProjectCollection;

    /**
     * Find a project by ID with relationships loaded.
     */
    public function findById(int $projectId, ?int $teamId = null): ProjectResource;

    /**
     * Create a new project with team context.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped project creation
     */
    public function createProject(array $data, ?int $teamId = null): ProjectResource;

    /**
     * Update a project by model instance.
     *
     * @param  array<string, mixed>  $data
     * @param  int|null  $teamId  Team ID for team-scoped updates
     */
    public function updateProject(Project $project, array $data, ?int $teamId = null): ProjectResource;

    /**
     * Delete a project by model instance.
     */
    public function deleteProject(Project $project): bool;
}
