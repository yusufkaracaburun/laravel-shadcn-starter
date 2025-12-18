<x-mail::message>
# Invoice #{{ $model->invoice_number }}

Hello,

Please find your invoice attached.

**Invoice Details:**
- **Invoice Number:** {{ $model->invoice_number }}
- **Total:** {{ $model->total->format() }}
- **Due Date:** {{ $model->date_due->format('M d, Y') }}

<x-mail::button :url="url('/invoices/' . $model->id)">
View Invoice
</x-mail::button>

Thanks,
{{ config('app.name') }}
</x-mail::message>