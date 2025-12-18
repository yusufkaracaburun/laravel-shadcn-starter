{{-- TOTALS --}}
<div class="totals">
    <table class="totals-table">
        <tr>
            <td>Subtotal</td>
            <td>VAT 0%</td>
            <td>VAT 9%</td>
            <td>VAT 21%</td>
            <td>Total</td>
        </tr>
        <tr class="totals-values">
            <td>{{ $data->subtotal->format() }}</td>
            <td>{{ $data->total_vat_0->format() }}</td>
            <td>{{ $data->total_vat_9->format() }}</td>
            <td>{{ $data->total_vat_21->format() }}</td>
            <td>{{ $data->total->format() }}</td>
        </tr>
    </table>
</div>
