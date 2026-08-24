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

<title>Payment Receipt PAY-{{ $payment->id }}</title>

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

h1 {
    margin: 0 0 10px;
    font-size: 22px;
    color: #ffffff;
}

h2 {
    margin: 16px 0 7px;
    font-size: 13px;
    color: #f5a623;
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

.right {
    text-align: right;
}

.box {
    margin-top: 14px;
    padding: 10px;
    border: 1px solid #666666;
    background: #111111;
    color: #ffffff;
}

.amount {
    margin-top: 20px;
    padding: 14px;
    border: 1px solid #f5a623;
    font-size: 18px;
    font-weight: bold;
    color: #f5a623;
    background: #111111;
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

    <h1>
        PAYMENT RECEIPT
    </h1>

    <div class="box">

        <strong>Receipt Number:</strong>
        PAY-{{ $payment->id }}<br><br>

        <strong>Date:</strong>
        {{ optional($payment->paid_at)->format('Y-m-d') }}<br><br>

        <strong>Customer:</strong>
        {{ $payment->customer->name ?? '-' }}<br>

        @if($payment->customer?->company_name)
            {{ $payment->customer->company_name }}<br>
        @endif

        @if($payment->customer?->email)
            {{ $payment->customer->email }}<br>
        @endif

        <br>

        <strong>Invoice:</strong>
        {{ $payment->invoice->number ?? '-' }}<br>

        <strong>Payment Method:</strong>
        {{ ucfirst($payment->method ?? '-') }}<br>

        <strong>Reference:</strong>
        {{ $payment->reference ?? '-' }}

    </div>

    @if($payment->items && $payment->items->count())

        <h2>Payment Items</h2>

        <table class="document-table">

            <thead>
                <tr>
                    <th>Description</th>
                    <th class="right">Qty</th>
                    <th class="right">Rate</th>
                    <th class="right">Amount</th>
                </tr>
            </thead>

            <tbody>

            @foreach($payment->items as $item)

                <tr>

                    <td>
                        {{ $item->description ?? 'Item' }}
                    </td>

                    <td class="right">
                        {{ $item->quantity }}
                    </td>

                    <td class="right">
                        LKR {{ number_format((float) $item->unit_price, 2) }}
                    </td>

                    <td class="right">
                        LKR {{ number_format((float) $item->total, 2) }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    @endif

    <div class="amount">

        Paid Amount:

        LKR {{ number_format((float) $payment->amount, 2) }}

    </div>

    @if($payment->notes)

        <div class="box">

            <strong>Notes</strong><br><br>

            {!! nl2br(e($payment->notes)) !!}

        </div>

    @endif

    <div class="box">

        <strong style="color:#f5a623;">
            Payment Details
        </strong>

        <br><br>

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