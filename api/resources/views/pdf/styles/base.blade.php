<style>
    /* =========================
       PAGE & LAYOUT
       ========================= */
    @page {
        margin: 30mm 20mm 30mm 20mm;
    }

    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11px;
        color: #333;
        margin: 0;
        line-height: 1.5;
    }

    .pdf-content {
        position: relative;
    }

    /* =========================
       HEADER
       ========================= */
    .pdf-header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;

        height: 25mm;
        padding: 5mm 20mm;

        font-size: 11px;
        color: #333;
        border-bottom: 1px solid #ddd;
        background: #fff;
    }

    .pdf-logo {
        max-height: 18mm;
        max-width: 60mm;
    }

    /* =========================
       FOOTER
       ========================= */
    .pdf-footer {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;

        height: 20mm;
        padding: 5mm 20mm;

        font-size: 10px;
        color: #777;
        border-top: 1px solid #ddd;
        background: #fff;
    }

    .page-number:before {
        content: counter(page);
    }

    .page-count:before {
        content: counter(pages);
    }

    /* =========================
       HELPERS
       ========================= */
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-muted { color: #777; }

    .mb-10 { margin-bottom: 10px; }
    .mb-20 { margin-bottom: 20px; }
    .mb-30 { margin-bottom: 30px; }

    /* =========================
       TABLE BASICS
       ========================= */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        vertical-align: top;
    }

    tr {
        page-break-inside: avoid;
    }
</style>
