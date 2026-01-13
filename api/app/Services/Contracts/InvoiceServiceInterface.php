<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Services\BaseServiceInterface;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;

interface InvoiceServiceInterface extends BaseServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): InvoiceCollection;

    public function getAll(array $columns = ['*']): InvoiceCollection;

    public function findById(int $id): InvoiceResource;

    public function createInvoice(array $data): InvoiceResource;

    public function updateInvoice(Invoice $invoice, array $data): InvoiceResource;

    public function deleteInvoice(Invoice $invoice): bool;

    public function getNextInvoiceNumber(string $prefix = 'INV', ?int $year = null): string;
}
