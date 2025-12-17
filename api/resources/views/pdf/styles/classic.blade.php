<style>
    /* =========================
       BASE
       ========================= */
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
        margin: 0;
        padding: 30px;
        line-height: 1.4;
    }

    h1, h2, h3 {
        margin: 0 0 10px 0;
        font-weight: bold;
    }

    h1 {
        font-size: 20px;
    }

    h2 {
        font-size: 16px;
    }

    p {
        margin: 0 0 6px 0;
    }

    /* =========================
       LAYOUT HELPERS
       ========================= */
    .mb-10 { margin-bottom: 10px; }
    .mb-20 { margin-bottom: 20px; }
    .mb-30 { margin-bottom: 30px; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-muted { color: #777; }

    /* =========================
       TABLES
       ========================= */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    th, td {
        padding: 8px;
        vertical-align: top;
    }

    .table-bordered th,
    .table-bordered td {
        border: 1px solid #ddd;
    }

    .table-bordered th {
        background-color: #f5f5f5;
        font-weight: bold;
    }

    /* Prevent rows from splitting across pages */
    tr {
        page-break-inside: avoid;
    }

    /* =========================
       HEADER
       ========================= */
    .invoice-header {
        width: 100%;
        margin-bottom: 30px;
    }

    .invoice-header td {
        vertical-align: top;
    }

    .company-details {
        font-size: 12px;
    }

    /* =========================
       CUSTOMER BLOCK
       ========================= */
    .customer-block {
        margin-bottom: 30px;
    }

    /* =========================
       TOTALS
       ========================= */
    .totals-table td {
        padding: 6px 8px;
    }

    .totals-table tr:last-child td {
        font-weight: bold;
        border-top: 2px solid #333;
    }

    /* =========================
       FOOTER
       ========================= */
    .footer {
        margin-top: 40px;
        font-size: 10px;
        color: #777;
        text-align: center;
    }
</style>
