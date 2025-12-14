<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface PaymentServiceInterface extends BaseServiceInterface
{
    public function getFilteredPayments(Request $request): LengthAwarePaginator;

    public function getById(int $id): Payment|Model|null;

    public function create(array $data): Payment|Model;

    public function update(int $id, array $data): Payment|Model;

    public function delete(int $id): bool;
}
