@extends('pdf.layout')

@section('content')

    {{-- KLANT + FACTUUR INFO --}}
    <table width="100%" class="mb-20">
        <tr>
            <td width="50%">
                <h2>Klant</h2>
                {{ $invoice->customer->name }}<br>
                {{ $invoice->customer->address }}<br>
                {{ $invoice->customer->zipcode }} {{ $invoice->customer->city }}
            </td>

            <td width="50%" class="text-right">
                <strong>Factuur</strong><br>
                Factuur #{{ $invoice->invoice_number }}<br>
                Datum {{ $invoice->date->format('d-m-Y') }}<br>
                Vervaldatum {{ $invoice->date_due->format('d-m-Y') }}
            </td>
        </tr>
    </table>

    {{-- ITEMS --}}
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
        @foreach ($invoice->items as $item)
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

    {{-- TOTALS --}}
    <div class="totals mb-30">
        <table>
            <tr>
                <td>Subtotaal (excl. BTW)</td>
                <td class="text-right">{{ $invoice->subtotal->format() }}</td>
            </tr>

            @if (! $invoice->total_vat_0->isZero())
                <tr>
                    <td>BTW 0%</td>
                    <td class="text-right">{{ $invoice->total_vat_0->format() }}</td>
                </tr>
            @endif

            @if (! $invoice->total_vat_9->isZero())
                <tr>
                    <td>BTW 9%</td>
                    <td class="text-right">{{ $invoice->total_vat_9->format() }}</td>
                </tr>
            @endif

            @if (! $invoice->total_vat_21->isZero())
                <tr>
                    <td>BTW 21%</td>
                    <td class="text-right">{{ $invoice->total_vat_21->format() }}</td>
                </tr>
            @endif

            <tr>
                <td><strong>Totaal (incl. BTW)</strong></td>
                <td class="text-right">
                    <strong>{{ $invoice->total->format() }}</strong>
                </td>
            </tr>
        </table>
    </div>

    {{-- NOTES --}}
    @if ($invoice->notes)
        <div class="notes mb-20">
            <h3>Opmerkingen</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    {{-- 🔒 FOOTER GUARD (VERPLICHT) --}}
    <div class="footer-guard"></div>

@endsection
