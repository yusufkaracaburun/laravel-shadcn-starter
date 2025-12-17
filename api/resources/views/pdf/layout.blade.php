<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Factuur</title>

    {{-- Base styles --}}
    @include('pdf.styles.base')

    {{-- Theme styles --}}
    @include('pdf.styles.' . ($invoice->template ?? 'minimal'))
</head>
<body>

{{-- HEADER (every page) --}}
@include('pdf.partials.header')

{{-- FOOTER (every page) --}}
@include('pdf.partials.footer')

{{-- CONTENT --}}
<main class="pdf-content">
    @yield('content')
</main>

</body>
</html>
