<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Teams\TeamCollection;

interface TeamServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): TeamCollection;

    public function getAll(array $columns = ['*']): TeamCollection;

    public function findById(int $id): TeamResource;

    public function create(array $data): TeamResource;

    public function update(Model $model, array $data): TeamResource;

    public function delete(Model $model): bool;
}
