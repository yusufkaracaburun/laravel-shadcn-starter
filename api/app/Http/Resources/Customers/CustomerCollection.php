<?php

declare(strict_types=1);

namespace App\Http\Resources\Customers;

use App\Http\Resources\BaseCollection;

final class CustomerCollection extends BaseCollection
{
    protected string $resourceClass = CustomerResource::class;
}
