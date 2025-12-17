{{-- ITEMS --}}
<div class="items">
    <table class="items-table mb-30">
        <thead>
        <tr>
            <th class="text-left">Name</th>
            <th class="text-center">Quantity</th>
            <th class="text-center">Unit</th>
            <th class="text-right">Unit Price</th>
            <th class="text-center">VAT Rate</th>
            <th class="text-right">Incl. VAT</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                <td class="text-center">uur</td>
                <td class="text-right">{{ $item->unit_price->format() }}</td>
                <td class="text-center">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="text-right">{{ $item->total_incl_vat->format() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
