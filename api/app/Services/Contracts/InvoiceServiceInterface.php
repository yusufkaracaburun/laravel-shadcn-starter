<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use App\Services\BaseServiceInterface;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;

interface InvoiceServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): InvoiceCollection;

    public function getAll(array $columns = ['*']): InvoiceCollection;

    public function findById(int $id): InvoiceResource;

    public function create(array $data): InvoiceResource;

    public function update(Model $model, array $data): InvoiceResource;

    public function delete(Model $model): bool;

    public function getNextInvoiceNumber(string $prefix = 'INV', ?int $year = null): string;
}
