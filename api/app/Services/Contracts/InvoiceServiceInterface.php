<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface InvoiceServiceInterface extends BaseServiceInterface
{
    public function getFilteredInvoices(Request $request): LengthAwarePaginator;

    public function getById(int $id): Invoice|Model|null;

    public function create(array $data): Invoice|Model;

    public function update(int $id, array $data): Invoice|Model;

    public function delete(int $id): bool;
}
