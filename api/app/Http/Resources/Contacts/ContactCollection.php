<?php

declare(strict_types=1);

namespace App\Http\Resources;

final class CustomerCollection extends BaseCollection
{
    protected string $resourceClass = CustomerResource::class;
}
