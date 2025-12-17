<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Factuur</title>

    @php
        $template = $invoice->template ?? 'modern';
    @endphp

    <style>
        {!! file_get_contents(resource_path('views/pdf/styles/base.css')) !!}
        {!! file_get_contents(resource_path("views/pdf/styles/{$template}.css")) !!}
    </style>
</head>
<body>

@include('pdf.partials.header')
@include('pdf.partials.footer')

<main class="pdf-content">
    @yield('content')
</main>

<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_text(
            20,
            $pdf->get_height() - 28,
            "Pagina {PAGE_NUM} van {PAGE_COUNT}",
            null,
            9,
            [0.4, 0.4, 0.4]
        );
    }
</script>

</body>
</html>
