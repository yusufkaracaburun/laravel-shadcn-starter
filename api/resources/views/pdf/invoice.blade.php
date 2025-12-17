@extends('pdf.layout')

@section('content')

    @include('pdf.partials.address', ['data' => $invoice])

    @include('pdf.partials.items', ['items' => $invoice->items])

    @include('pdf.partials.totals', ['data' => $invoice])

    @include('pdf.partials.notes', ['notes' => $invoice->notes])

@endsection
