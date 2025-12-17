<div class="pdf-header">
    <table width="100%">
        <tr>
            <td class="header-left">
                <img src="{{ public_path('logo.png') }}" class="pdf-logo" alt="Logo">
            </td>

            <td class="header-center">
                <div class="invoice-title">FACTUUR</div>
                <div class="invoice-meta">
                    Factuur #{{ $invoice->invoice_number }}<br>
                    Datum {{ $invoice->date->format('d-m-Y') }}<br>
                    Vervaldatum {{ $invoice->date_due->format('d-m-Y') }}
                </div>
            </td>

            <td class="header-right">
                <strong>Bedrijfsnaam</strong><br>
                Bedrijfsadres<br>
                Bedrijfsplaats<br>
                BTW nummer
            </td>
        </tr>
    </table>
</div>
