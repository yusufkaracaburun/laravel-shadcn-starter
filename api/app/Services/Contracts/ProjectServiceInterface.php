<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\Projects\ProjectCollection;

interface ProjectServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProjectCollection;

    public function getAll(array $columns = ['*']): ProjectCollection;

    public function findById(int $id): ProjectResource;

    public function createProject(array $data): ProjectResource;

    public function updateProject(Project $project, array $data): ProjectResource;

    public function deleteProject(Project $project): bool;
}
