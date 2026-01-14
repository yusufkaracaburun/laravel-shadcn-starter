<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Services\Concerns\TransformsResources;
use App\Http\Resources\Equipments\EquipmentResource;
use App\Services\Contracts\EquipmentServiceInterface;
use App\Http\Resources\Equipments\EquipmentCollection;
use App\Repositories\Contracts\EquipmentRepositoryInterface;

final class EquipmentService extends BaseService implements EquipmentServiceInterface
{
    use TransformsResources;

    public function __construct(
        EquipmentRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): EquipmentCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): EquipmentCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): EquipmentResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): EquipmentResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Equipment $model
     */
    public function update(Model $model, array $data): EquipmentResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Equipment $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return EquipmentResource::class;
    }

    protected function getCollectionClass(): string
    {
        return EquipmentCollection::class;
    }
}
