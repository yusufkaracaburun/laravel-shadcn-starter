<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use App\Services\BaseService;
use App\Services\Concerns\TransformsResources;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentCollection;
use App\Services\Contracts\PaymentServiceInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;

final class PaymentService extends BaseService implements PaymentServiceInterface
{
    use TransformsResources;

    public function __construct(
        PaymentRepositoryInterface $repository,
    ) {
        $this->setRepository($repository);
    }

    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PaymentCollection
    {
        return $this->toCollection(
            $this->repository->paginateFiltered($request, $columns),
        );
    }

    public function getAll(array $columns = ['*']): PaymentCollection
    {
        return $this->toCollection(
            $this->repository->all($columns),
        );
    }

    public function findById(int $id): PaymentResource
    {
        $payment = $this->repository->find($id);

        return $this->toResource($payment->load(['invoice', 'customer']));
    }

    public function create(array $data): PaymentResource
    {
        return $this->toResource(
            $this->repository->create($data),
        );
    }

    /**
     * @param Payment $model
     */
    public function update(Model $model, array $data): PaymentResource
    {
        return $this->toResource(
            $this->repository->update($model->id, $data),
        );
    }

    /**
     * @param Payment $model
     */
    public function delete(Model $model): bool
    {
        return $this->repository->delete($model->id);
    }

    protected function getResourceClass(): string
    {
        return PaymentResource::class;
    }

    protected function getCollectionClass(): string
    {
        return PaymentCollection::class;
    }
}
