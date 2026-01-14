<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepositoryInterface;
use App\Services\Concerns\TransformsResources;

abstract class BaseService implements BaseServiceInterface
{
    use TransformsResources;

    protected QueryableRepositoryInterface $repository;

    public function getPaginatedByRequest(array $columns = ['*']): mixed
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($columns),
        );
    }

    public function getAll(array $columns = ['*']): mixed
    {
        return $this->toCollection(
            $this->repository->getFiltered($columns),
        );
    }

    public function find(int|string $id): mixed
    {
        return $this->toResource(
            $this->repository->findOrFail($id),
        );
    }

    public function create(array $data): mixed
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Model $model
     * @param array $data
     * @return mixed
     */
    public function update($model, array $data): mixed
    {
        return $this->toResource(
            $this->repository->update($model, $data),
        );
    }

    /**
     * @param Model $model
     * @return bool
     */
    public function delete($model): bool
    {
        return $this->repository->delete($model);
    }

    final public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface
    {
        return $this->repository = $repository;
    }

    final public function getRepository(): QueryableRepositoryInterface
    {
        return $this->repository;
    }

    public function exists(int|string $id): bool
    {
        return $this->repository->exists($id);
    }
}
