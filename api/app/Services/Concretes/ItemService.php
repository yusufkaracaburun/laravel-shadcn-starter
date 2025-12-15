<?php

declare(strict_types=1);

namespace App\Services\Concretes;

use App\Models\Item;
use App\Services\BaseService;
use App\Http\Resources\ItemResource;
use App\Http\Resources\ItemCollection;
use App\Services\Contracts\ItemServiceInterface;
use App\Repositories\Contracts\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class ItemService extends BaseService implements ItemServiceInterface
{
    private readonly ItemRepositoryInterface $itemRepository;

    /**
     * Create a new class instance.
     */
    public function __construct(
        ItemRepositoryInterface $repo
    ) {
        $this->setRepository($repo);
        $this->itemRepository = $repo;
    }

    public function getPaginated(int $perPage, ?int $teamId = null): ItemCollection
    {
        $request = request();
        $request->query->set('per_page', (string) $perPage);

        $this->itemRepository->withRequest($request);

        $paginated = $this->itemRepository->query()->paginate($perPage);

        return new ItemCollection($paginated);
    }

    public function findById(int $itemId, ?int $teamId = null): ItemResource
    {
        try {
            $item = $this->itemRepository->findOrFail($itemId);

            return new ItemResource($item);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }

    public function createItem(array $data, ?int $teamId = null): ItemResource
    {
        $item = $this->itemRepository->create($data);

        return new ItemResource($item);
    }

    public function updateItem(Item $item, array $data, ?int $teamId = null): ItemResource
    {
        try {
            $updated = $this->itemRepository->update($item->id, $data);

            return new ItemResource($updated);
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }

    public function deleteItem(Item $item): bool
    {
        try {
            $this->itemRepository->delete($item->id);

            return true;
        } catch (ModelNotFoundException) {
            throw new ModelNotFoundException('Item not found');
        }
    }
}
