@php
    $company = \App\Models\CompanySetting::query()->first();

    $letterheadData = null;

    if ($company?->letterhead_path) {
        $possiblePaths = [
            public_path($company->letterhead_path),

            storage_path(
                'app/public/' . ltrim(
                    str_replace(
                        ['storage/', '/storage/'],
                        '',
                        $company->letterhead_path
                    ),
                    '/'
                )
            ),
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($path)) {
                $mime = mime_content_type($path) ?: 'image/png';

                $letterheadData =
                    'data:' . $mime . ';base64,' .
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

<title>
    Payment Receipt PAY-{{ $payment->id }}
</title>


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
    min-height: 297mm;
}

body {
    font-family: DejaVu Sans, Arial, sans-serif;
    font-size: 11px;
    color: #ffffff;
}

/*
|--------------------------------------------------------------------------
| Letterhead Background
|--------------------------------------------------------------------------
*/

.letterhead-background {
    position: fixed;
    top: 0;
    left: 0;
    width: 210mm;
    height: 297mm;
    z-index: -1000;
}

.letterhead-background img {
    width: 210mm;
    height: 297mm;
}

/*
|--------------------------------------------------------------------------
| Content
|--------------------------------------------------------------------------
*/

.page {
    position: relative;
    padding: 210px 70px 145px 70px;
}

h1 {
    margin: 0 0 12px;
    font-size: 24px;
    color: #ffffff;
}

h2 {
    margin: 18px 0 8px;
    font-size: 14px;
    color: #f5a623;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 16px;
}

th,
td {
    border-bottom: 1px solid #777777;
    padding: 8px;
}

th {
    color: #f5a623;
    background: #111111;
    text-align: left;
}

.right {
    text-align: right;
}

.box {
    margin-top: 18px;
    padding: 12px;
    border: 1px solid #666666;
    background: #111111;
}

.amount {
    margin-top: 20px;
    font-size: 22px;
    font-weight: bold;
    color: #f5a623;
}

</style>

</head>


<body>

@if($letterheadData)

    <div class="letterhead-background">

        <img
            src="{{ $letterheadData }}"
            alt="Letterhead"
        >

    </div>

@endif


<div class="page">


    {{-- Receipt Header --}}

    <h1>

        {{ $company?->company_name ?? 'Crow.lk (Pvt) Ltd' }}

        — PAYMENT RECEIPT

    </h1>


    {{-- Payment Information --}}

    <div class="box">

        <strong>
            Receipt:
        </strong>

        PAY-{{ $payment->id }}

        <br>


        <strong>
            Date:
        </strong>

        {{ optional($payment->paid_at)->format('Y-m-d') }}

        <br>


        <strong>
            Customer:
        </strong>

        {{ $payment->customer->name ?? '-' }}

        <br>


        <strong>
            Invoice:
        </strong>

        {{ $payment->invoice->number ?? '-' }}

        <br>


        <strong>
            Payment Method:
        </strong>

        {{ ucfirst($payment->method ?? '-') }}

        <br>


        <strong>
            Reference:
        </strong>

        {{ $payment->reference ?? '-' }}

    </div>


    {{-- Payment Items --}}

    @if($payment->items && $payment->items->count())

        <h2>
            Payment Items
        </h2>


        <table>

            <thead>

            <tr>

                <th>
                    Description
                </th>


                <th class="right">
                    Qty
                </th>


                <th class="right">
                    Rate
                </th>


                <th class="right">
                    Amount
                </th>

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
                        {{ number_format((float) $item->unit_price, 2) }}
                    </td>


                    <td class="right">
                        {{ number_format((float) $item->total, 2) }}
                    </td>

                </tr>

            @endforeach

            </tbody>

        </table>

    @endif


    {{-- Paid Amount --}}

    <div class="amount">

        Paid:

        LKR
        {{ number_format((float) $payment->amount, 2) }}

    </div>


    {{-- Notes --}}

    @if($payment->notes)

        <div class="box">

            {!! nl2br(e($payment->notes)) !!}

        </div>

    @endif


    {{-- Bank Details --}}

    <div class="box">

        <strong>
            Payment Details
        </strong>

        <br>

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