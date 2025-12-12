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

                'current_page' => $this->currentPage(),
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
                'path' => $this->path(),
                'first_page_url' => $this->url(1),
                'last_page_url' => $this->url($this->lastPage()),
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
            ];
        }

        return $data->toArray($request);
    }
}
