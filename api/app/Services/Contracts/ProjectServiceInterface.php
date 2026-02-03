<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\Projects\ProjectCollection;

interface ProjectServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProjectCollection;

    public function getAll(array $columns = ['*']): ProjectCollection;

    public function findById(int $id): ProjectResource;

    public function create(array $data): ProjectResource;

    public function update(Model $model, array $data): ProjectResource;

    public function delete(Model $model): bool;
}
