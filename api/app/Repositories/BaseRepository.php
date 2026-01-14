<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Http\Request;
use InvalidArgumentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\Relation;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected Builder|Model|Relation $model;
    protected int $DEFAULT_PER_PAGE = 10;

    public function __construct()
    {
        $this->setModel($this->model());
    }

    abstract protected function model(): string;

    final public function setModel(Model|Builder|Relation|string $entity): void
    {
        if (is_string($entity)) {
            $this->model = resolve($entity)->query();
        } elseif ($entity instanceof Model) {
            $this->model = $entity->newQuery();
        } else {
            $this->model = $entity;
        }
    }

    public function newQuery(): Builder
    {
        if ($this->model instanceof Builder) {
            return $this->model;
        }

        if ($this->model instanceof Relation) {
            return $this->model->getQuery();
        }

        return $this->model->newQuery();
    }

    final public function all(array $columns = ['*']): Collection
    {
        return $this->newQuery()->get($columns);
    }

    final public function paginate(int $perPage = 10, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->newQuery()->paginate($perPage, $columns);
    }

    final public function find(int|string $id, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->find($id, $columns);
    }

    final public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->where($field, $value)->first($columns);
    }

    public function findOrFail(int|string $id, array $columns = ['*']): Model
    {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    final public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    final public function update(Model $model, array $data): Model
    {
        $model->update($data);

        return $model->fresh();
    }

    final public function delete(Model $model): bool
    {
        return (bool) $model->delete();
    }

    final public function exists(int|string $id): bool
    {
        return $this->newQuery()->where('id', $id)->exists();
    }

    final public function getModel(): Model
    {
        return $this->newQuery()->getModel();
    }
}
