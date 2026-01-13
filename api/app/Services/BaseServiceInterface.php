<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepositoryInterface;

interface BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): mixed;

    public function getAll(array $columns = ['*']): mixed;

    public function findById(int $id): mixed;

    public function create(array $data): mixed;

    public function update(Model $model, array $data): mixed;

    public function delete(Model $model): bool;

    public function exists(int $id): bool;

    public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface;

    public function getRepository(): QueryableRepositoryInterface;
}
