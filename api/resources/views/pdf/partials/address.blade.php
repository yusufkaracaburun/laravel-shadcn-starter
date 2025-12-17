{{-- KLANT + FACTUUR INFO --}}
<div class="address">
    <table class="mb-20">
        <tr>
            <td class="customer" width="50%">
                <h2>Klant</h2>
                {{ $data->customer->name }}<br>
                {{ $data->customer->address }}<br>
                {{ $data->customer->zipcode }} {{ $data->customer->city }}
            </td>

            <td width="50%" class="invoice-info text-right">
                <strong>Factuur</strong><br>
                Factuur #{{ $data->invoice_number }}<br>
                Datum {{ $data->date->format('d-m-Y') }}<br>
                Vervaldatum {{ $data->date_due->format('d-m-Y') }}
            </td>
        </tr>
    </table>
</div>
