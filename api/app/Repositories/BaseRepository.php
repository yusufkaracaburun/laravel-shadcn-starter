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

    public function newQuery(): Builder
    {
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

    final public function find(int $id, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->find($id, $columns);
    }

    final public function findByField(string $field, mixed $value, array $columns = ['*']): ?Model
    {
        return $this->newQuery()->where($field, $value)->first($columns);
    }

    public function findOrFail(int $id, array $columns = ['*']): Model
    {
        return $this->newQuery()->findOrFail($id, $columns);
    }

    final public function create(array $data): Model
    {
        return $this->newQuery()->create($data);
    }

    final public function update(int $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    final public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    final public function exists(int $id): bool
    {
        return $this->newQuery()->where('id', $id)->exists();
    }

    final public function getModel(): Model
    {
        return $this->model;
    }
}
