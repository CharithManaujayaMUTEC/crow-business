@php
    $company = \App\Models\CompanySetting::query()->first();

    $letterheadPath = $company?->letterhead_path
        ? public_path($company->letterhead_path)
        : null;

    $letterheadData = ($letterheadPath && is_file($letterheadPath))
        ? 'data:image/png;base64,' . base64_encode(file_get_contents($letterheadPath))
        : null;
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
}

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
    color: #ffffff;
    min-height: 100%;
    @if($letterheadData)
    background-image: url('{{ $letterheadData }}');
    background-size: 100% 100%;
    background-repeat: no-repeat;
    @else
    background: #000000;
    @endif
}

.page {
    padding: 210px 70px 145px 70px;
}

h1 {
    margin: 0 0 6px;
    font-size: 24px;
    color: #ffffff;
}

h2 {
    margin: 18px 0 8px;
    font-size: 14px;
    color: #f5a623;
}

.muted {
    color: #dddddd;
}

.right {
    text-align: right;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

th,
td {
    border-bottom: 1px solid rgba(255,255,255,.35);
    padding: 8px;
}

th {
    text-align: left;
    color: #f5a623;
    background: rgba(0,0,0,.40);
}

.right-cell {
    text-align: right;
}

.total {
    font-size: 14px;
    font-weight: bold;
    color: #f5a623;
}

.box {
    margin-top: 18px;
    padding: 12px;
    border: 1px solid rgba(255,255,255,.30);
    background: rgba(0,0,0,.42);
}
</style>
</head>

<body>
<div class="page">

    <table style="margin-top:0">
        <tr>
            <td style="border:0">
                <h1>{{ $company?->company_name ?? 'Crow.lk (Pvt) Ltd' }}</h1>
                <div class="muted">Invoice</div>
            </td>

            <td style="border:0" class="right">
                <h1>INVOICE</h1>
                <strong>{{ $invoice->number }}</strong><br>
                Issued: {{ optional($invoice->issued_at)->format('Y-m-d') }}<br>
                Due: {{ optional($invoice->due_at)->format('Y-m-d') }}
            </td>
        </tr>
    </table>

    <h2>Bill To</h2>

    {{ $invoice->customer->name ?? '-' }}<br>
    {{ $invoice->customer->company_name ?? '' }}<br>
    {{ $invoice->customer->email ?? '' }}<br>
    {{ $invoice->customer->phone ?? '' }}

    @if($invoice->description)
        <div class="box">
            {!! nl2br(e($invoice->description)) !!}
        </div>
    @endif

    <h2>Items</h2>

    <table>
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
                <td>{{ $item->description ?? 'Item' }}</td>
                <td class="right-cell">{{ $item->quantity }}</td>
                <td class="right-cell">{{ number_format((float) $item->unit_price, 2) }}</td>
                <td class="right-cell">{{ number_format((float) $item->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">No line items.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <table>
        <tr>
            <td class="right-cell">Subtotal</td>
            <td class="right-cell">{{ number_format((float) $invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td class="right-cell">Discount</td>
            <td class="right-cell">{{ number_format((float) $invoice->discount, 2) }}</td>
        </tr>
        <tr>
            <td class="right-cell">Tax</td>
            <td class="right-cell">{{ number_format((float) $invoice->tax, 2) }}</td>
        </tr>
        <tr>
            <td class="right-cell total">Total (LKR)</td>
            <td class="right-cell total">{{ number_format((float) $invoice->total, 2) }}</td>
        </tr>
    </table>

    <div class="box">
        <strong>Outstanding Balance:</strong>
        LKR {{ number_format((float) $invoice->balance, 2) }}
    </div>

    @if($invoice->notes)
        <div class="box">
            <strong>Notes</strong><br>
            {!! nl2br(e($invoice->notes)) !!}
        </div>
    @endif

    <div class="box">
        <strong>Payment Details</strong><br>
        Account Name: {{ $company?->bank_account_name ?? '-' }}<br>
        Bank: {{ $company?->bank_name ?? '-' }}<br>
        Branch: {{ $company?->bank_branch ?? '-' }}<br>
        Account Number: {{ $company?->bank_account_number ?? '-' }}
    </div>

</div>
</body>
</html>