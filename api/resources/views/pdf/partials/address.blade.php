{{-- KLANT + FACTUUR INFO --}}
<div class="address">
    <table>
        <tr>
            <td class="customer" width="50%">
                <h2>Klant</h2>
                {{ $data->customer->name }}<br>
                {{ $data->customer->address }}<br>
                {{ $data->customer->zipcode }} {{ $data->customer->city }}
            </td>

            <td width="50%" class="invoice-info">
                <h2>Factuur</h2>
                Factuur #{{ $data->invoice_number }}<br>
                Datum {{ $data->date->format('d-m-Y') }}<br>
                Vervaldatum {{ $data->date_due->format('d-m-Y') }}
            </td>
        </tr>
    </table>
</div>
