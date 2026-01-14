<?php

declare(strict_types=1);

namespace App\Services\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\JsonResource;

trait TransformsResources
{
    /**
     * Get the resource class name for this service.
     * Override in concrete services if needed.
     */
    abstract protected function getResourceClass(): string;

    /**
     * Get the collection class name for this service.
     * Override in concrete services if needed.
     */
    abstract protected function getCollectionClass(): string;

    /**
     * Transform a model into a resource.
     */
    protected function toResource(Model $model): JsonResource
    {
        $resourceClass = $this->getResourceClass();

        return new $resourceClass($model);
    }

    /**
     * Transform a collection or paginator into a collection resource.
     */
    protected function toCollection(Collection|LengthAwarePaginator $items): mixed
    {
        $collectionClass = $this->getCollectionClass();

        return new $collectionClass($items);
    }
}
