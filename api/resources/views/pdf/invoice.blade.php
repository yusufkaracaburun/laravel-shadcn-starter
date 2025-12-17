@extends('pdf.layout')

@section('content')

    @include('pdf.partials.address', ['data' => $invoice])

    @include('pdf.partials.items', ['items' => $invoice->items])

    @include('pdf.partials.totals', ['data' => $invoice])

    {{-- NOTES --}}
    @if ($invoice->notes)
        <div class="notes mb-20">
            <h3>Opmerkingen</h3>
            <p>{{ $invoice->notes }}</p>
        </div>
    @endif

@endsection
