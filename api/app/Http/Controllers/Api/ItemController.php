<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Models\Item;
use App\Filters\SearchFilter;
use Illuminate\Http\JsonResponse;
use App\Http\Responses\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use Spatie\QueryBuilder\AllowedFilter;
use App\Http\Requests\Items\IndexItemRequest;
use App\Http\Requests\Items\StoreItemRequest;
use App\Http\Requests\Items\UpdateItemRequest;
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

        $perPage = (int) ($request->input('per_page', 15));

        $items = $this->buildQuery(
            Item::query(),
            allowedFilters: [
                AllowedFilter::exact('id'),
                AllowedFilter::exact('name'),
                AllowedFilter::exact('unit'),
                AllowedFilter::exact('unit_price'),
                AllowedFilter::exact('vat_rate'),
                AllowedFilter::scope('created_at', 'created_at'),
                AllowedFilter::scope('updated_at', 'updated_at'),
                AllowedFilter::scope('between', 'created_at'),
                AllowedFilter::custom('search', new SearchFilter(['name', 'description'])),
            ],
            allowedSorts: [
                'id',
                'name',
                'unit_price',
                'vat_rate',
                'unit',
                'created_at',
                'updated_at',
            ],
            allowedIncludes: ['invoiceLines']
        )->paginate($perPage);

        return ApiResponse::success(new ItemCollection($items));
    }

    /**
     * Store a newly created item.
     *
     * @authenticated
     */
    public function store(StoreItemRequest $request): JsonResponse
    {
        $this->authorize('create', Item::class);

        $item = Item::create($request->validated());

        return ApiResponse::created(new ItemResource($item));
    }

    /**
     * Display the specified item.
     *
     * @authenticated
     */
    public function show(Item $item): JsonResponse
    {
        $this->authorize('view', $item);

        return ApiResponse::success(new ItemResource($item));
    }

    /**
     * Update the specified item.
     *
     * @authenticated
     */
    public function update(UpdateItemRequest $request, Item $item): JsonResponse
    {
        $this->authorize('update', $item);

        $item->update($request->validated());

        return ApiResponse::success(new ItemResource($item));
    }

    /**
     * Remove the specified item.
     *
     * @authenticated
     */
    public function destroy(Item $item): JsonResponse
    {
        $this->authorize('delete', $item);

        $item->delete();

        return ApiResponse::noContent('Item deleted successfully');
    }
}
