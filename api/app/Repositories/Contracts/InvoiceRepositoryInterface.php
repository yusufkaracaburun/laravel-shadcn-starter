<?php

declare(strict_types=1);

namespace App\Repositories\Contracts;

use App\Models\Invoice;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Repositories\QueryableRepositoryInterface;

interface InvoiceRepositoryInterface extends QueryableRepositoryInterface
{
}
