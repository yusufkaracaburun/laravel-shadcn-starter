<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;
use App\Services\Concerns\TransformsResources;
use App\Services\Contracts\CustomerServiceInterface;
use App\Repositories\Contracts\CustomerRepositoryInterface;

final class CustomerService extends BaseService implements CustomerServiceInterface
{
    use TransformsResources;

    public function __construct(
        CustomerRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): CustomerCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): CustomerCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): CustomerResource
    {
        return $this->toResource(
            $this->repository->find($id),
        );
    }

    public function create(array $data): CustomerResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Customer $model
     */
    public function update(Model $model, array $data): CustomerResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Customer $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return CustomerResource::class;
    }

    protected function getCollectionClass(): string
    {
        return CustomerCollection::class;
    }
}
