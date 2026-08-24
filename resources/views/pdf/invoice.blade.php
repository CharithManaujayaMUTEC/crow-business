@php
    $company = \App\Models\CompanySetting::query()->first();

    $letterheadData = null;

    if ($company?->letterhead_path) {
        $possiblePaths = [
            public_path($company->letterhead_path),
            storage_path('app/public/' . ltrim(str_replace('storage/', '', $company->letterhead_path), '/')),
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
    margin: 0;
}

html,
body {
    margin: 0;
    padding: 0;
    width: 210mm;
    height: 297mm;
}

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 10px;
    color: #ffffff;
    background: #000000;
}

.letterhead {
    position: fixed;
    top: 0;
    left: 0;
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
    padding: 62mm 18mm 40mm 18mm;
}

.header-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0 0 12px 0;
}

.header-table td {
    border: none;
    padding: 0;
    vertical-align: top;
}

h1 {
    margin: 0 0 5px;
    font-size: 22px;
    color: #ffffff;
}

h2 {
    margin: 16px 0 7px;
    font-size: 13px;
    color: #f5a623;
}

.muted {
    color: #dddddd;
}

.right {
    text-align: right;
}

.document-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 12px;
}

.document-table th,
.document-table td {
    padding: 8px 6px;
    border-bottom: 1px solid #777777;
}

.document-table th {
    color: #f5a623;
    background: #151515;
    text-align: left;
}

.document-table td {
    color: #ffffff;
}

.right-cell {
    text-align: right;
}

.totals-table {
    width: 48%;
    margin-left: auto;
    border-collapse: collapse;
    margin-top: 14px;
}

.totals-table td {
    padding: 6px;
    border-bottom: 1px solid #555555;
}

.total {
    color: #f5a623;
    font-size: 13px;
    font-weight: bold;
}

.box {
    margin-top: 14px;
    padding: 10px;
    border: 1px solid #666666;
    background: #111111;
    color: #ffffff;
}

.amount {
    color: #f5a623;
    font-size: 13px;
    font-weight: bold;
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
        <tr>
            <td>
                <h1>{{ $company?->company_name ?? 'Crow.lk (Pvt) Ltd' }}</h1>

                <div class="muted">
                    Invoice
                </div>
            </td>

            <td class="right">
                <h1>INVOICE</h1>

                <strong>{{ $invoice->number }}</strong><br>

                Issued:
                {{ optional($invoice->issued_at)->format('Y-m-d') }}<br>

                Due:
                {{ optional($invoice->due_at)->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <h2>Bill To</h2>

    <div>
        <strong>{{ $invoice->customer->name ?? '-' }}</strong><br>

        @if($invoice->customer?->company_name)
            {{ $invoice->customer->company_name }}<br>
        @endif

        @if($invoice->customer?->email)
            {{ $invoice->customer->email }}<br>
        @endif

        @if($invoice->customer?->phone)
            {{ $invoice->customer->phone }}
        @endif
    </div>

    @if($invoice->description)
        <div class="box">
            <strong>Description</strong><br><br>

            {!! nl2br(e($invoice->description)) !!}
        </div>
    @endif

    <h2>Items</h2>

    <table class="document-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="right-cell">Qty</th>
                <th class="right-cell">Unit Price</th>
                <th class="right-cell">Amount</th>
            </tr>
        </thead>

        <tbody>
        @forelse($invoice->items as $item)
            <tr>
                <td>
                    {{ $item->description ?? 'Item' }}
                </td>

                <td class="right-cell">
                    {{ $item->quantity }}
                </td>

                <td class="right-cell">
                    LKR {{ number_format((float) $item->unit_price, 2) }}
                </td>

                <td class="right-cell">
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
            <td class="right-cell">
                LKR {{ number_format((float) $invoice->subtotal, 2) }}
            </td>
        </tr>

        <tr>
            <td>Discount</td>
            <td class="right-cell">
                LKR {{ number_format((float) $invoice->discount, 2) }}
            </td>
        </tr>

        <tr>
            <td>Tax</td>
            <td class="right-cell">
                LKR {{ number_format((float) $invoice->tax, 2) }}
            </td>
        </tr>

        <tr>
            <td class="total">Total</td>
            <td class="right-cell total">
                LKR {{ number_format((float) $invoice->total, 2) }}
            </td>
        </tr>
    </table>

    <div class="box">
        <strong>Outstanding Balance:</strong>

        <span class="amount">
            LKR {{ number_format((float) $invoice->balance, 2) }}
        </span>
    </div>

    @if($invoice->notes)
        <div class="box">
            <strong>Notes</strong><br><br>

            {!! nl2br(e($invoice->notes)) !!}
        </div>
    @endif

    <div class="box">
        <strong style="color:#f5a623;">Payment Details</strong><br><br>

        Account Name:
        {{ $company?->bank_account_name ?? 'Crow.lk (Pvt) Ltd' }}<br>

        Bank:
        {{ $company?->bank_name ?? 'HNB' }}<br>

        Branch:
        {{ $company?->bank_branch ?? 'Pettah' }}<br>

        Account Number:
        {{ $company?->bank_account_number ?? '007010350044' }}
    </div>

</div>

</body>
</html>