<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Item;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Items\IndexItemRequest;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
use App\Services\Contracts\ItemServiceInterface;
use App\Http\Controllers\Concerns\UsesQueryBuilder;
use App\Http\Controllers\Concerns\UsesCachedResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Http\Controllers\Concerns\InvalidatesCachedModels;

final class ItemController extends Controller
{
    use AuthorizesRequests;
    use InvalidatesCachedModels;
    use UsesCachedResponses;
    use UsesQueryBuilder;

    public function __construct(
        private readonly ItemServiceInterface $itemService
    ) {}

    /**
     * Display a listing of items with QueryBuilder support.
     *
     * Supports filtering, sorting, and including relationships via request parameters.
     * Example: /api/items?filter[name]=test&sort=-created_at&include=invoiceLines
     *
     * @authenticated
     */
    public function index(IndexItemRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Item::class);

        $validated = $request->validated();
        $perPage = (int) $validated['per_page'];

        $items = $this->itemService->getPaginated($perPage);

        return ApiResponse::success($items);
    }

    /**
     * Store a newly created item.
     *
     * @authenticated
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $this->authorize('create', Item::class);

        $item = $this->itemService->createItem($request->validated());

        return ApiResponse::created($item);
    }

    /**
     * Display the specified item.
     *
     * @authenticated
     */
    public function show(Item $item): JsonResponse
    {
        $this->authorize('view', $item);

        $itemResource = $this->itemService->findById($item->id);

        return ApiResponse::success($itemResource);
    }

    /**
     * Update the specified item.
     *
     * @authenticated
     */
    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $this->authorize('update', $item);

        $itemResource = $this->itemService->updateItem($item, $request->validated());

        return ApiResponse::success($itemResource);
    }

    /**
     * Remove the specified item.
     *
     * @authenticated
     */
    public function destroy(Item $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $this->itemService->deleteItem($item);

        return ApiResponse::noContent('Item deleted successfully');
    }
}
