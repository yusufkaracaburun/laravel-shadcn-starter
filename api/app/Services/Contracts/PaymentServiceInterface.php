<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\Payments\PaymentResource;
use App\Http\Resources\Payments\PaymentCollection;

interface PaymentServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): PaymentCollection;

    public function getAll(array $columns = ['*']): PaymentCollection;

    public function findById(int $id): PaymentResource;

    public function create(array $data): PaymentResource;

    public function update(Model $model, array $data): PaymentResource;

    public function delete(Model $model): bool;
}
