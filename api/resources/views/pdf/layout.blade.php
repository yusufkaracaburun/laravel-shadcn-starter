<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Factuur</title>

    <style>
        {!! file_get_contents(resource_path('views/pdf/styles/base.css')) !!}
        {!! file_get_contents(resource_path('views/pdf/styles/' . ($invoice->template ?? 'modern') . '.css')) !!}
    </style>
</head>
<body>

@include('pdf.partials.header')
@include('pdf.partials.footer')

<main class="pdf-content">
    @yield('content')
</main>

{{--<script type="text/php">--}}
{{--    if (isset($pdf)) {--}}
{{--        $pdf->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {--}}

{{--            $text = "Pagina {$pageNumber} / {$pageCount}";--}}

{{--            $font = $fontMetrics->getFont("Helvetica", "normal");--}}
{{--            $size = 7;--}}

{{--            // A4 breedte ≈ 595pt--}}
{{--            // Rechtsboven in header:--}}
{{--            $textWidth = $fontMetrics->getTextWidth($text, $font, $size);--}}

{{--            $x = 595 - 60 - $textWidth; // rechter marge (60mm ≈ 79pt)--}}
{{--            $y = 30;                    // binnen header-hoogte--}}

{{--            $canvas->text($x, $y, $text, $font, $size, [0.4, 0.4, 0.4]);--}}
{{--        });--}}
{{--    }--}}
{{--</script>--}}
</body>
</html>
