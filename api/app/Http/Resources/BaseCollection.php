<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    /**
     * Specify which resource should wrap each individual item.
     * Example in child: protected string $resourceClass = UserResource::class;
     */
    protected string $resourceClass;

    public function toArray($request): array
    {
        return [
            'data' => ($this->resourceClass)::collection($this->collection),

            'current_page' => $this->currentPage(),
            'last_page'     => $this->lastPage(),
            'per_page'      => $this->perPage(),
            'total'         => $this->total(),
            'from'          => $this->firstItem(),
            'to'            => $this->lastItem(),
        ];
    }
}