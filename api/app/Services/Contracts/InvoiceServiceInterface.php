<?php

declare(strict_types=1);

namespace App\Services\Contracts;

use App\Models\Invoice;
use App\Services\BaseServiceInterface;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceCollection;

interface InvoiceServiceInterface extends BaseServiceInterface
{
    /**
     * Get paginated invoices with QueryBuilder support.
     * Supports filtering, sorting, and including relationships via request parameters.
     */
    public function getPaginated(int $perPage, ?int $teamId = null): InvoiceCollection;

    /**
     * Find an invoice by ID with relationships loaded.
     */
    public function findById(int $invoiceId, ?int $teamId = null): InvoiceResource;

    /**
     * Create a new invoice with optional team context.
     *
     * @param  array<string, mixed>  $data
     */
    public function createInvoice(array $data, ?int $teamId = null): InvoiceResource;

    /**
     * Update an invoice by model instance.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateInvoice(Invoice $invoice, array $data, ?int $teamId = null): InvoiceResource;

    /**
     * Delete an invoice by model instance.
     */
    public function deleteInvoice(Invoice $invoice): bool;
}
