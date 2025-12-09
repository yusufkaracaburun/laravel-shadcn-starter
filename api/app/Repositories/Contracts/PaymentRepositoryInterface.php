<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Payment;
use App\Repositories\QueryableRepositoryInterface;

interface PaymentRepositoryInterface extends QueryableRepositoryInterface
{
    public function findOrFail(int $id, array $columns = ['*']): Payment;
}
