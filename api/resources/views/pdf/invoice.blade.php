@extends('pdf.layout')

@section('content')

    {{-- HEADER --}}
    <div class="invoice-header">
        <table>
            <tr>
                <td>
                    <h1>Factuur</h1>
                    <p class="text-muted">
                        Factuur #{{ $invoice->invoice_number }}<br>
                        Datum {{ $invoice->date->format('d-m-Y') }}<br>
                        Vervaldatum {{ $invoice->date_due->format('d-m-Y') }}
                    </p>
                </td>
                <td class="company-details">
                    <strong>Bedrijfsnaam</strong><br>
                    Bedrijfsadres<br>
                    Bedrijfsplaats<br>
                    BTW nummer
                </td>
            </tr>
        </table>
    </div>

    {{-- CUSTOMER --}}
    <div class="customer-block">
        <h2>Klant</h2>
        {{ $invoice->customer->name }}<br>
        {{ $invoice->customer->address }}<br>
        {{ $invoice->customer->zipcode }} {{ $invoice->customer->city }}
    </div>

    {{-- ITEMS --}}
    <table>
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
    <div class="totals">
        <table>
            {{-- Subtotaal --}}
            <tr>
                <td>Subtotaal (excl. BTW)</td>
                <td class="text-right">{{ $invoice->subtotal->format() }}</td>
            </tr>

            {{-- BTW per tarief als er waarde is --}}
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

            {{-- Totaal incl. BTW --}}
            <tr>
                <td><strong>Totaal (incl. BTW)</strong></td>
                <td class="text-right"><strong>{{ $invoice->total->format() }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- NOTES --}}
    @if ($invoice->notes)
        <div class="notes">
            <h3>Opmerkingen</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

    <div style="clear: both;"></div>

    {{-- FOOTER --}}
    <div class="footer">
        Vragen over deze factuur? Neem gerust contact op.
    </div>

@endsection
