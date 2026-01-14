<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use App\Services\Concerns\TransformsResources;
use App\Services\Concerns\HandlesRelationships;
use App\Http\Resources\Vehicles\VehicleResource;
use App\Http\Resources\Vehicles\VehicleCollection;
use App\Services\Contracts\VehicleServiceInterface;
use App\Repositories\Contracts\VehicleRepositoryInterface;

final class VehicleService extends BaseService implements VehicleServiceInterface
{
    use HandlesRelationships;

    public function __construct(
        VehicleRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(array $columns = ['*']): VehicleCollection
    {
        /** @var VehicleCollection */
        return parent::getPaginatedByRequest($columns);
    }

    public function getAll(array $columns = ['*']): VehicleCollection
    {
        /** @var VehicleCollection */
        return parent::getAll($columns);
    }

    public function find(int|string $id): VehicleResource
    {
        /** @var VehicleResource */
        return parent::find($id);
    }

    public function create(array $data): VehicleResource
    {
        $vehicle = $this->repository->create($this->exceptRelationships($data, ['drivers']));
        $this->syncRelationships($vehicle, $data, ['drivers']);

        /** @var VehicleResource */
        return $this->toResource($vehicle->load('drivers'));
    }

    public function update(Vehicle $model, array $data): VehicleResource
    {
        $updated = $this->repository->update($model, $this->exceptRelationships($data, ['drivers']));
        $this->syncRelationships($updated, $data, ['drivers']);

        /** @var VehicleResource */
        return $this->toResource($updated->load('drivers'));
    }

    public function delete(Vehicle $model): bool
    {
        return $this->repository->delete($model);
    }

    protected function getResourceClass(): string
    {
        return VehicleResource::class;
    }

    protected function getCollectionClass(): string
    {
        return VehicleCollection::class;
    }
}
