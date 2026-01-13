<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
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

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): mixed
    {
        return $this->repository->paginateFiltered($request, $columns);
    }

    public function getAll(array $columns = ['*']): mixed
    {
        return $this->repository->all($columns);
    }

    public function findById(int $id): mixed
    {
        return $this->repository->find($id);
    }

    public function create(array $data): mixed
    {
        return $this->repository->create($data);
    }

    public function update(Model $model, array $data): mixed
    {
        return $this->repository->update($model->id, $data);
    }

    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    public function exists(int $id): bool
    {
        return $this->repository->exists($id);
    }
}
