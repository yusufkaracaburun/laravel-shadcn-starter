<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface BaseServiceInterface
{
    public function getFiltered(array $columns = ['*']): Collection;

    public function all(array $columns = ['*']): Collection;

    public function paginate(int $perPage = 25, array $columns = ['*']): LengthAwarePaginator;

    public function find(int $id, array $columns = ['*']): ?Model;

    public function findOrFail(int $id, array $columns = ['*']): Model;

    public function create(array $data): Model;

    public function update(int $id, array $data): Model;

    public function delete(int $id): bool;

    public function exists(int $id): bool;

    public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface;

    public function getRepository(): QueryableRepositoryInterface;
}
