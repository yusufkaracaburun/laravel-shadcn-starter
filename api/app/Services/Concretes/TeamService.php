<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Team;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\Teams\TeamResource;
use App\Http\Resources\Teams\TeamCollection;
use App\Services\Concerns\TransformsResources;
use App\Services\Contracts\TeamServiceInterface;
use App\Repositories\Contracts\TeamRepositoryInterface;

final class TeamService extends BaseService implements TeamServiceInterface
{
    use TransformsResources;

    public function __construct(
        TeamRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): TeamCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): TeamCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): TeamResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): TeamResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Team $model
     */
    public function update(Model $model, array $data): TeamResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Team $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return TeamResource::class;
    }

    protected function getCollectionClass(): string
    {
        return TeamCollection::class;
    }
}
