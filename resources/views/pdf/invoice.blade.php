<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice {{ $invoice->number }}</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#222}
.header{display:flex;justify-content:space-between;margin-bottom:30px}
h1{font-size:24px;margin:0 0 8px}.muted{color:#666}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{border:1px solid #ddd;padding:8px;text-align:left}
th{background:#f3f4f6}.right{text-align:right}.total{font-size:15px;font-weight:bold}
.balance{margin-top:15px;padding:10px;border:1px solid #ddd}
.footer{margin-top:35px;font-size:10px;color:#777}
</style>
</head>
<body>
<div class="header">
<div><h1>Crow.lk</h1><div class="muted">Digital & Business Solutions</div></div>
<div class="right"><h1>INVOICE</h1><strong>{{ $invoice->number }}</strong><br>
Issued: {{ optional($invoice->issued_at)->format('Y-m-d') }}<br>
Due: {{ optional($invoice->due_at)->format('Y-m-d') }}</div>
</div>
<h3>Bill To</h3>
{{ $invoice->customer->name ?? '-' }}<br>
{{ $invoice->customer->company_name ?? '' }}<br>
{{ $invoice->customer->email ?? '' }}<br>
{{ $invoice->customer->phone ?? '' }}

<table>
<thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Unit Price</th><th class="right">Amount</th></tr></thead>
<tbody>
@if($invoice->items && $invoice->items->count())
@foreach($invoice->items as $item)
<tr>
<td>{{ $item->description ?? $item->name ?? 'Item' }}</td>
<td class="right">{{ $item->quantity ?? 1 }}</td>
<td class="right">{{ number_format((float)($item->unit_price ?? 0),2) }}</td>
<td class="right">{{ number_format((float)($item->amount ?? (($item->quantity ?? 1)*($item->unit_price ?? 0))),2) }}</td>
</tr>
@endforeach
@else
<tr><td>Invoice services/products</td><td class="right">1</td><td class="right">{{ number_format((float)$invoice->subtotal,2) }}</td><td class="right">{{ number_format((float)$invoice->subtotal,2) }}</td></tr>
@endif
</tbody>
</table>
<table>
<tr><td class="right">Subtotal</td><td class="right">{{ number_format((float)$invoice->subtotal,2) }}</td></tr>
<tr><td class="right">Discount</td><td class="right">{{ number_format((float)$invoice->discount,2) }}</td></tr>
<tr><td class="right">Tax</td><td class="right">{{ number_format((float)$invoice->tax,2) }}</td></tr>
<tr><td class="right total">Total (LKR)</td><td class="right total">{{ number_format((float)$invoice->total,2) }}</td></tr>
</table>
<div class="balance"><strong>Outstanding Balance:</strong> LKR {{ number_format((float)$invoice->balance,2) }}</div>
@if($invoice->notes)<h3>Notes</h3><p>{!! nl2br(e($invoice->notes)) !!}</p>@endif
<div class="footer">Thank you for your business.</div>
</body>
</html>
