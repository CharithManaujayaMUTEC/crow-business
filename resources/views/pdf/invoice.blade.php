@php
    $company = \App\Models\CompanySetting::query()->first();

    $logoData = null;

    $logoPath = public_path('images/crow-logo.png');

    if (is_file($logoPath)) {
        $logoMime = mime_content_type($logoPath) ?: 'image/png';

        $logoData = 'data:' . $logoMime . ';base64,' .
            base64_encode(file_get_contents($logoPath));
    }

    $letterheadData = null;

    {
        $possiblePaths = [
            public_path($company?->letterhead_path ?: 'images/crow-company-letterhead.png'),
            public_path('images/crow-company-letterhead.png'),
            storage_path('app/public/' . ltrim(str_replace('storage/', '', (string) $company?->letterhead_path), '/')),
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/png';

                $letterheadData = 'data:' . $mime . ';base64,' .
                    base64_encode(file_get_contents($path));

                break;
            }
        }
    }
@endphp

<!doctype html>
<html>
<head>
<meta charset="utf-8">

<title>Invoice {{ $invoice->number }}</title>

<style>

@page {
    size: A4;
    margin: 50mm 18mm 38mm 18mm;
}

body {
    margin: 0;
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10px;
    color: #1a1a1a;
    
}

.letterhead {
    position: fixed;
    top: -50mm;
    left: -18mm;
    width: 210mm;
    height: 297mm;
    z-index: 0;
}

.letterhead img {
    width: 210mm;
    height: 297mm;
}

.page {
    position: relative;
    z-index: 1;
    padding: 0;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin: 0 0 12px 0;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

.logo-cell {
    text-align: left;
    vertical-align: top;
}

.company-logo {
    width: 42mm;
    height: auto;
}

h1 {
    margin: 0 0 5px;
    font-size: 22px;
    color: #1a1a1a;
}

h2 {
    margin: 16px 0 7px;
    font-size: 13px;
    color: #e8890c;
}

.muted {
    color: #444444;
}

.right {
    text-align: right;
}

.document-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 12px;
}

.document-table th,
.document-table td {
    padding: 8px 6px;
    border-bottom: 1px solid #cccccc;
    vertical-align: middle;
}

.document-table th {
    color: #e8890c;
    background: #f1f1f1;
    text-align: left;
}

.document-table td {
    color: #1a1a1a;
}

.document-table th:nth-child(2),
.document-table th:nth-child(3),
.document-table th:nth-child(4),
.document-table td:nth-child(2),
.document-table td:nth-child(3),
.document-table td:nth-child(4) {
    text-align: right;
    white-space: nowrap;
}

.description-cell {
    word-wrap: break-word;
    overflow-wrap: break-word;
}

.totals-table {
    width: 50%;
    margin-left: auto;
    border-collapse: collapse;
    table-layout: fixed;
    margin-top: 14px;
}

.totals-table td {
    padding: 7px 6px;
    border-bottom: 1px solid #cccccc;
}

.totals-table td:first-child {
    width: 55%;
}

.totals-table td:last-child {
    width: 45%;
    text-align: right;
    white-space: nowrap;
}

.total {
    color: #e8890c;
    font-size: 13px;
    font-weight: bold;
}

.box {
    margin-top: 14px;
    padding: 10px;
    border: 1px solid #cccccc;
    background: #f7f7f7;
    color: #1a1a1a;
}


.box,
.bank-box,
.totals-table,
.document-table tr,
h2 {
    page-break-inside: avoid;
}

h2 {
    page-break-after: avoid;
}

.document-table thead {
    display: table-header-group;
}

</style>
</head>

<body>

@if($letterheadData)
<div class="letterhead">
    <img src="{{ $letterheadData }}" alt="Letterhead">
</div>
@endif

<div class="page">

    <table class="header-table">

        <colgroup>
            <col style="width: 55%">
            <col style="width: 45%">
        </colgroup>

        <tr>

            <td class="logo-cell">

            </td>

            <td class="right">

                <h1>INVOICE</h1>

                <strong>{{ $invoice->number }}</strong>
                <br>

                Issued:
                {{ optional($invoice->issued_at)->format('Y-m-d') }}
                <br>

                Due:
                {{ optional($invoice->due_at)->format('Y-m-d') }}

            </td>

        </tr>

    </table>


    <h2>Bill To</h2>

    {{ $invoice->customer->name ?? '-' }}<br>

    @if($invoice->customer?->company_name)
        {{ $invoice->customer->company_name }}<br>
    @endif

    @if($invoice->customer?->email)
        {{ $invoice->customer->email }}<br>
    @endif

    @if($invoice->customer?->phone)
        {{ $invoice->customer->phone }}
    @endif


    @if($invoice->description)

        <div class="box">
            {!! nl2br(e($invoice->description)) !!}
        </div>

    @endif


    <h2>Items</h2>


    <table class="document-table">

        <colgroup>
            <col style="width: 45%">
            <col style="width: 12%">
            <col style="width: 21%">
            <col style="width: 22%">
        </colgroup>

        <thead>

            <tr>
                <th>Description</th>
                <th>Qty</th>
                <th>Unit Price</th>
                <th>Amount</th>
            </tr>

        </thead>

        <tbody>

        @forelse($invoice->items as $item)

            <tr>

                <td class="description-cell">
                    {{ $item->description ?? 'Item' }}
                </td>

                <td>
                    {{ number_format((float) $item->quantity, 2) }}
                </td>

                <td>
                    LKR {{ number_format((float) $item->unit_price, 2) }}
                </td>

                <td>
                    LKR {{ number_format((float) $item->total, 2) }}
                </td>

            </tr>

        @empty

            <tr>
                <td colspan="4">
                    No line items.
                </td>
            </tr>

        @endforelse

        </tbody>

    </table>


    <table class="totals-table">

        <tr>
            <td>Subtotal</td>

            <td>
                LKR {{ number_format((float) $invoice->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td>Discount</td>

            <td>
                LKR {{ number_format((float) $invoice->discount, 2) }}
            </td>
        </tr>

        <tr>
            <td>Tax</td>

            <td>
                LKR {{ number_format((float) $invoice->tax, 2) }}
            </td>
        </tr>

        <tr>
            <td class="total">
                Total
            </td>

            <td class="total">
                LKR {{ number_format((float) $invoice->total, 2) }}
            </td>
        </tr>

    </table>


    <div class="box">

        <strong>Outstanding Balance:</strong>

        LKR {{ number_format((float) $invoice->balance, 2) }}

    </div>


    @if($invoice->notes)

        <div class="box">

            <strong>Notes</strong>
            <br>

            {!! nl2br(e($invoice->notes)) !!}

        </div>

    @endif


    <div class="box">

        <strong>Payment Details</strong>
        <br><br>

        Account Name:
        {{ $company?->bank_account_name ?? '-' }}
        <br>

        Bank:
        {{ $company?->bank_name ?? '-' }}
        <br>

        Branch:
        {{ $company?->bank_branch ?? '-' }}
        <br>

        Account Number:
        {{ $company?->bank_account_number ?? '-' }}

    </div>

</div>

</body>
</html>
