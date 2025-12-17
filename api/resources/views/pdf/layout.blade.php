<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Default layout</title>

    @include('pdf.styles.' . ($invoice->template ?? 'classic'))
</head>
<body>
@yield('content')
</body>
</html>
