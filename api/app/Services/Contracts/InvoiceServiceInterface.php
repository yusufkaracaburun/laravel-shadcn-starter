<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;

interface InvoiceServiceInterface
{
    public function getPaginatedByRequest(Request $request, array $columns = ['*']): InvoiceCollection;

    public function getAll(): InvoiceCollection;

    public function findById(int $invoiceId, ?int $teamId = null): InvoiceResource;

    public function createInvoice(array $data, ?int $teamId = null): InvoiceResource;

    public function updateInvoice(Invoice $invoice, array $data, ?int $teamId = null): InvoiceResource;

    public function deleteInvoice(Invoice $invoice): bool;

    public function getNextInvoiceNumber(string $prefix = 'INV', ?int $year = null): string;
}
