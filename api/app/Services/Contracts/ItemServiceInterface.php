<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ItemServiceInterface extends BaseServiceInterface
{
    public function getFilteredItems(Request $request): LengthAwarePaginator;

    public function getById(int $id): Item|Model|null;

    public function create(array $data): Item|Model;

    public function update(int $id, array $data): Item|Model;

    public function delete(int $id): bool;
}
