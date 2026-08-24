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
    table-layout: fixed;
    margin-top: 12px;
}

.document-table th,
.document-table td {
    padding: 8px 6px;
    border-bottom: 1px solid #777777;
    vertical-align: middle;
}

.document-table th {
    color: #f5a623;
    background: #151515;
    text-align: left;
}

.document-table td {
    color: #ffffff;
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


    <div class="box">

        <strong>Receipt:</strong>
        PAY-{{ $payment->id }}
        <br>

        <strong>Date:</strong>
        {{ optional($payment->paid_at)->format('Y-m-d') }}
        <br>

        <strong>Customer:</strong>
        {{ $payment->customer->name ?? '-' }}
        <br>

        <strong>Invoice:</strong>
        {{ $payment->invoice->number ?? '-' }}
        <br>

        <strong>Payment Method:</strong>
        {{ ucfirst($payment->method ?? '-') }}
        <br>

        <strong>Reference:</strong>
        {{ $payment->reference ?? '-' }}

    </div>


    @if($payment->items && $payment->items->count())

        <h2>Payment Items</h2>

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
                    <th>Rate</th>
                    <th>Amount</th>
                </tr>

            </thead>

            <tbody>

            @foreach($payment->items as $item)

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

            @endforeach

            </tbody>

        </table>

    @endif


    <div class="amount">

        Paid:
        LKR {{ number_format((float) $payment->amount, 2) }}

    </div>


    @if($payment->notes)

        <div class="box">

            {!! nl2br(e($payment->notes)) !!}

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