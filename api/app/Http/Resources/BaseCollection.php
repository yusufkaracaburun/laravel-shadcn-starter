<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;

abstract class BaseCollection extends ResourceCollection
{
    /**
     * Specify which resource should wrap each individual item.
     * Example in child: protected string $resourceClass = UserResource::class;
     */
    protected string $resourceClass;

    /**
     * Transform the resource collection into an array.
     * Handles both paginated and non-paginated collections.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    final public function toArray($request): array
    {
        $data = ($this->resourceClass)::collection($this->collection);

        if ($this->resource instanceof AbstractPaginator) {
            return [
                'data' => $data,
                'meta' => [
                    'current_page' => $this->currentPage(),
                    'last_page' => $this->lastPage(),
                    'per_page' => $this->perPage(),
                    'total' => $this->total(),
                    'from' => $this->firstItem(),
                    'to' => $this->lastItem(),
                ],
                'links' => [
                    'first' => $this->url(1),
                    'last' => $this->url($this->lastPage()),
                    'prev' => $this->previousPageUrl(),
                    'next' => $this->nextPageUrl(),
                ],
            ];
        }

        return $data->toArray($request);
    }
}
