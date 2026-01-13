<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

abstract class BaseService implements BaseServiceInterface
{
    protected QueryableRepositoryInterface $repository;

    final public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface
    {
        return $this->repository = $repository;
    }

    final public function getRepository(): QueryableRepositoryInterface
    {
        return $this->repository;
    }

    final public function getFiltered(array $columns = ['*']): Collection
    {
        return $this->repository->getFiltered($columns);
    }

    final public function all(array $columns = ['*']): Collection
    {
        return $this->repository->all($columns);
    }

    final public function paginate(int $perPage = 25, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->repository->paginate($perPage, $columns);
    }

    final public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->repository->find($id, $columns);
    }

    final public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->repository->findOrFail($id, $columns);
    }

    final public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    final public function update(int $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    final public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }

    final public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }
}
