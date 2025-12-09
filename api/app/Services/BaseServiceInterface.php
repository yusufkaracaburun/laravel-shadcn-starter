<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface BaseServiceInterface
{
    /**
     * Get filtered, sorted, and included resources.
     */
    public function getFiltered(array $columns = ['*']): Collection;

    /**
     * Get all resources
     */
    public function all(array $columns = ['*']): Collection;

    /**
     * Get paginated resources
     */
    public function paginate(int $perPage = 25, array $columns = ['*']): LengthAwarePaginator;

    /**
     * Find resource by id
     */
    public function find(int $id, array $columns = ['*']): ?Model;

    /**
     * Find resource or fail
     */
    public function findOrFail(int $id, array $columns = ['*']): Model;

    /**
     * Create new resource
     */
    public function create(array $data): Model;

    /**
     * Update resource
     */
    public function update(int $id, array $data): Model;

    /**
     * Delete resource
     */
    public function delete(int $id): bool;

    /**
     * Check if resource exists
     */
    public function exists(int $id): bool;

    /**
     * Set repository instance
     */
    public function setRepository(QueryableRepositoryInterface $repository): QueryableRepositoryInterface;

    /**
     * Get repository instance
     */
    public function getRepository(): QueryableRepositoryInterface;
}
