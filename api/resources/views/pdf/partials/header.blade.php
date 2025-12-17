<div class="pdf-header">
    <div class="pdf-header-inner">
        @php
            $logo = app()->runningInConsole()
                ? public_path('logo.png')   // PDF
                : asset('logo.png');        // HTML
        @endphp

        <img src="{{ $logo }}" class="pdf-logo" alt="Logo">
    </div>
</div>
