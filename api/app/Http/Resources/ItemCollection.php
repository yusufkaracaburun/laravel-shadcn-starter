<?php

declare(strict_types=1);

namespace App\Http\Resources;

final class ItemCollection extends BaseCollection
{
    /**
     * The resource that this resource collects.
     */
    protected string $resourceClass = ItemResource::class;
}
