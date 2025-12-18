<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Mail\Mailables\Attachment;

final class InvoiceMail extends BaseMail
{
    public function __construct(public readonly Invoice $invoice)
    {
        //
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, Attachment>
     */
    public function attachments(): array
    {
        $response = $this->invoice->generatePdf()->stream();
        $name = 'invoice_' . $this->invoice->invoice_number . '.pdf';

        return [
            Attachment::fromData(fn () => $response, $name)
                ->withMime('application/pdf'),
        ];
    }

    protected function getModel(): Invoice
    {
        return $this->invoice;
    }

    protected function getSubject(): string
    {
        return 'Invoice ' . $this->invoice->invoice_number;
    }

    protected function getContentMarkdown(): string
    {
        return 'emails.invoices.sent';
    }
}
