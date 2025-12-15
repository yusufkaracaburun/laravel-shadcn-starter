<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Item;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Services\BaseServiceInterface;

interface ItemServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated items with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): ItemCollection;

    /**
     * Find an item by ID with relationships loaded.
     */
    public function findById(int $itemId, ?int $teamId = null): ItemResource;

    /**
     * Create a new item with optional team context.
     *
     * @param  array<string, mixed>  $data
     */
    public function createItem(array $data, ?int $teamId = null): ItemResource;

    /**
     * Update an item by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(Item $item, array $data, ?int $teamId = null): ItemResource;

    /**
     * Delete an item by model instance.
     */
    public function deleteItem(Item $item): bool;
}
