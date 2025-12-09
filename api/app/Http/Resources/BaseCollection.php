<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Resources\Json\PaginatedResourceResponse;

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
        // If paginated, return pagination structure
        if ($this->resource instanceof AbstractPaginator) {
            return [
                'data' => ($this->resourceClass)::collection($this->collection),

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

        // For non-paginated collections, return simple structure
        return ($this->resourceClass)::collection($this->collection)->toArray($request);
    }

    /**
     * Add extra metadata to the response.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    final public function with($request): array
    {
        return [
            'meta' => $this->getMeta(),
        ];
    }

    /**
     * Override toResponse to return our custom pagination format
     * instead of Laravel's default format with links and meta.
     */
    final public function toResponse($request)
    {
        return $this->resource instanceof AbstractPaginator
            ? (new PaginatedResourceResponse($this))->toResponse($request)
            : parent::toResponse($request);
    }

    /**
     * Get meta information for the collection.
     *
     * @return array<string, mixed>
     */
    protected function getMeta(): array
    {
        return [
            'count' => $this->collection->count(),
        ];
    }
}
