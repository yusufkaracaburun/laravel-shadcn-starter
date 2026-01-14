<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Repositories\QueryableRepositoryInterface;

interface BaseServiceInterface
{
    public function getPaginatedByRequest(array $columns = ['*']): mixed;

    public function getAll(array $columns = ['*']): mixed;

    public function find(int|string $id): mixed;

    public function create(array $data): mixed;

    /**
     * @param mixed $model
     * @param array $data
     * @return mixed
     */
    public function update($model, array $data): mixed;

    /**
     * @param mixed $model
     * @return bool
     */
    public function delete($model): bool;

    public function exists(int|string $id): bool;

    public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface;

    public function getRepository(): QueryableRepositoryInterface;
}
