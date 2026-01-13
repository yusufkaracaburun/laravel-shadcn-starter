<?php

declare(strict_types=1);

namespace App\Repositories;

use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Builder|Model|Relation $model;

    /**
     * BaseRepository constructor.
     */
    public function __construct()
    {
        $this->setModel($this->model());
    }

    /**
     * Specify Model class name
     */
    abstract protected function model(): string;

    /**
     * Set new model. It can be: bare model, QueryBuilder, Relation,
     */
    final public function setModel(Model|Builder|Relation|string $entity): void
    {
        if (is_a($entity, Model::class) || is_subclass_of($entity, Model::class)) {
            $this->model = $entity::query();
        } elseif (
            is_a($entity, Builder::class) ||
            is_subclass_of($entity, Builder::class) ||
            is_a($entity, Relation::class) ||
            is_subclass_of($entity, Relation::class)
        ) {
            $this->model = $entity;
        } elseif (is_string($entity)) {
            $this->model = resolve($entity)->query();
        } else {
            throw new InvalidArgumentException('Invalid entity type');
        }
    }

    /**
     * Get all resources
     */
    final public function all(array $columns = ['*']): Collection
    {
        return $this->model->get($columns);
    }

    /**
     * Get paginated resources
     */
    final public function paginate(int $perPage = 10, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find resource by id
     */
    final public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find resource by field
     */
    final public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->model->where($field, $value)->first($columns);
    }

    /**
     * Find resource or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->model->findOrFail($id, $columns);
    }

    /**
     * Create new resource
     */
    final public function create(array $data): Model
    {
        return $this->model->create($data);
    }

    /**
     * Update resource
     */
    final public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * Delete resource
     */
    final public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * Check resource exists
     */
    final public function exists(int $id): bool
    {
        return $this->model->where('id', $id)->exists();
    }

    /**
     * Get model instance
     */
    final public function getModel(): Model
    {
        return $this->model;
    }
}
