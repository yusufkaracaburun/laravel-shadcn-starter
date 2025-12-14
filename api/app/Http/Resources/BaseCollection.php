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
            // Return Laravel's standard pagination format that frontend expects
            return [
                'data' => $data->toArray($request),
                'current_page' => $this->currentPage(),
                'per_page' => $this->perPage(),
                'total' => $this->total(),
                'last_page' => $this->lastPage(),
                'first_page_url' => $this->url(1),
                'last_page_url' => $this->url($this->lastPage()),
                'next_page_url' => $this->nextPageUrl(),
                'prev_page_url' => $this->previousPageUrl(),
                'path' => $this->path(),
                'from' => $this->firstItem(),
                'to' => $this->lastItem(),
            ];
        }

        return $data->toArray($request);
    }
}
