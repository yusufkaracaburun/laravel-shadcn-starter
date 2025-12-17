{{-- ITEMS --}}
<div class="items">
    <table class="mb-30">
        <thead>
        <tr>
            <th>Omschrijving</th>
            <th class="text-center">Aantal</th>
            <th class="text-center">BTW%</th>
            <th class="text-right">Prijs (excl.)</th>
            <th class="text-right">Totaal (excl.)</th>
        </tr>
        </thead>
        <tbody>
        @foreach ($items as $item)
            <tr>
                <td>{{ $item->description }}</td>
                <td class="text-center">{{ $item->quantity }}</td>
                <td class="text-center">{{ number_format($item->vat_rate, 0) }}%</td>
                <td class="text-right">{{ $item->unit_price->format() }}</td>
                <td class="text-right">{{ $item->total_excl_vat->format() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
