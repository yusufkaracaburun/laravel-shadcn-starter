<?php

declare(strict_types=1);

namespace App\Http\Resources\Products;

use App\Http\Resources\BaseCollection;

final class ProductCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     */
    protected string $resourceClass = ProductResource::class;
}
