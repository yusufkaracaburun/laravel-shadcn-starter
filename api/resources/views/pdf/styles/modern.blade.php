<style>
    /* =========================
       BASE
       ========================= */
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 11.5px;
        color: #1f2937; /* slate-800 */
        margin: 0;
        padding: 40px;
        line-height: 1.5;
    }

    h1, h2 {
        margin: 0;
        font-weight: bold;
    }

    h1 {
        font-size: 22px;
        color: #0f172a; /* slate-900 */
    }

    h2 {
        font-size: 13px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #2563eb; /* blue-600 */
        margin-bottom: 8px;
    }

    p {
        margin: 0 0 6px 0;
    }

    /* =========================
       HELPERS
       ========================= */
    .mb-10 { margin-bottom: 10px; }
    .mb-20 { margin-bottom: 20px; }
    .mb-30 { margin-bottom: 30px; }

    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-muted { color: #6b7280; } /* gray-500 */

    /* =========================
       HEADER
       ========================= */
    .invoice-header {
        margin-bottom: 35px;
        border-bottom: 3px solid #2563eb;
        padding-bottom: 15px;
    }

    .company-details {
        font-size: 11px;
        text-align: right;
    }

    /* =========================
       CUSTOMER
       ========================= */
    .customer-block {
        margin-bottom: 30px;
        padding-left: 10px;
        border-left: 3px solid #2563eb;
    }

    /* =========================
       TABLES
       ========================= */
    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead th {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        text-align: left;
        padding: 10px 6px;
        border-bottom: 2px solid #e5e7eb;
    }

    tbody td {
        padding: 10px 6px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: top;
    }

    tr {
        page-break-inside: avoid;
    }

    /* =========================
       TOTALS
       ========================= */
    .totals {
        width: 45%;
        float: right;
        margin-top: 20px;
        background: #f8fafc; /* slate-50 */
        padding: 15px;
        border-radius: 4px;
    }

    .totals table td {
        padding: 6px 0;
    }

    .totals tr:last-child td {
        border-top: 2px solid #2563eb;
        padding-top: 10px;
        font-size: 13px;
        font-weight: bold;
    }

    /* =========================
       FOOTER
       ========================= */
    .footer {
        margin-top: 60px;
        font-size: 10px;
        color: #6b7280;
        text-align: center;
    }
</style>
