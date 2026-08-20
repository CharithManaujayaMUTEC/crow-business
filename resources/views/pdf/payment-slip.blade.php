<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Payment Slip</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#222}
.box{border:1px solid #ddd;padding:25px}
h1{margin-top:0}.row{padding:7px 0;border-bottom:1px solid #eee}
.amount{font-size:22px;font-weight:bold;margin-top:20px}
.footer{margin-top:30px;font-size:10px;color:#777}
</style>
</head>
<body>
<div class="box">
<h1>Crow.lk — PAYMENT RECEIPT</h1>
<div class="row"><strong>Receipt:</strong> PAY-{{ $payment->id }}</div>
<div class="row"><strong>Date:</strong> {{ optional($payment->paid_at)->format('Y-m-d') }}</div>
<div class="row"><strong>Customer:</strong> {{ $payment->customer->name ?? '-' }}</div>
<div class="row"><strong>Invoice:</strong> {{ $payment->invoice->number ?? '-' }}</div>
<div class="row"><strong>Payment Method:</strong> {{ ucfirst($payment->method ?? '-') }}</div>
<div class="row"><strong>Reference:</strong> {{ $payment->reference ?? '-' }}</div>
<div class="amount">Paid: LKR {{ number_format((float)$payment->amount,2) }}</div>
@if($payment->notes)<p>{!! nl2br(e($payment->notes)) !!}</p>@endif
<div class="footer">This is a computer-generated payment receipt.</div>
</div>
</body>
</html>
