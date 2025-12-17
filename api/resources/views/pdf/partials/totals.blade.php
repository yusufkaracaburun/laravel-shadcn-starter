{{-- TOTALS --}}
<div class="totals mb-30">
    <table>
        <tr>
            <td>Subtotaal (excl. BTW)</td>
            <td class="text-right">{{ $data->subtotal->format() }}</td>
        </tr>

        @if (! $data->total_vat_0->isZero())
            <tr>
                <td>BTW 0%</td>
                <td class="text-right">{{ $data->total_vat_0->format() }}</td>
            </tr>
        @endif

        @if (! $data->total_vat_9->isZero())
            <tr>
                <td>BTW 9%</td>
                <td class="text-right">{{ $data->total_vat_9->format() }}</td>
            </tr>
        @endif

        @if (! $data->total_vat_21->isZero())
            <tr>
                <td>BTW 21%</td>
                <td class="text-right">{{ $data->total_vat_21->format() }}</td>
            </tr>
        @endif

        <tr>
            <td><strong>Totaal (incl. BTW)</strong></td>
            <td class="text-right">
                <strong>{{ $data->total->format() }}</strong>
            </td>
        </tr>
    </table>
</div>
