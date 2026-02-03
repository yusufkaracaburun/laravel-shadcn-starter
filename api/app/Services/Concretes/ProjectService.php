<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Project;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use App\Services\Concerns\TransformsResources;
use App\Http\Resources\Projects\ProjectResource;
use App\Http\Resources\Projects\ProjectCollection;
use App\Services\Contracts\ProjectServiceInterface;
use App\Repositories\Contracts\ProjectRepositoryInterface;

final class ProjectService extends BaseService implements ProjectServiceInterface
{
    use TransformsResources;

    public function __construct(
        ProjectRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): ProjectCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): ProjectCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): ProjectResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): ProjectResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Project $model
     */
    public function update(Model $model, array $data): ProjectResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Project $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return ProjectResource::class;
    }

    protected function getCollectionClass(): string
    {
        return ProjectCollection::class;
    }
}
