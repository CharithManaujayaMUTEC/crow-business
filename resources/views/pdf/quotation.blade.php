<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Quotation {{ $quotation->number }}</title>
<style>
body{font-family:DejaVu Sans,Arial,sans-serif;font-size:12px;color:#222}
.header{display:flex;justify-content:space-between;margin-bottom:30px}
h1{font-size:24px;margin:0 0 8px}.muted{color:#666}
table{width:100%;border-collapse:collapse;margin-top:20px}
th,td{border:1px solid #ddd;padding:8px;text-align:left}
th{background:#f3f4f6}.right{text-align:right}
.total{font-size:15px;font-weight:bold}
.footer{margin-top:35px;font-size:10px;color:#777}
</style>
</head>
<body>
<div class="header">
<div>
<h1>Crow.lk</h1>
<div class="muted">Digital & Business Solutions</div>
</div>
<div class="right">
<h1>QUOTATION</h1>
<strong>{{ $quotation->number }}</strong><br>
Issued: {{ optional($quotation->issued_at)->format('Y-m-d') }}<br>
Valid until: {{ optional($quotation->valid_until)->format('Y-m-d') }}
</div>
</div>
<h3>Bill To</h3>
{{ $quotation->customer->name ?? '-' }}<br>
{{ $quotation->customer->company_name ?? '' }}<br>
{{ $quotation->customer->email ?? '' }}<br>
{{ $quotation->customer->phone ?? '' }}

<table>
<thead><tr><th>Description</th><th class="right">Qty</th><th class="right">Unit Price</th><th class="right">Amount</th></tr></thead>
<tbody>
@if($quotation->items && $quotation->items->count())
@foreach($quotation->items as $item)
<tr>
<td>{{ $item->description ?? $item->name ?? 'Item' }}</td>
<td class="right">{{ $item->quantity ?? 1 }}</td>
<td class="right">{{ number_format((float)($item->unit_price ?? 0),2) }}</td>
<td class="right">{{ number_format((float)($item->amount ?? (($item->quantity ?? 1)*($item->unit_price ?? 0))),2) }}</td>
</tr>
@endforeach
@else
<tr><td>Quotation services/products</td><td class="right">1</td><td class="right">{{ number_format((float)$quotation->subtotal,2) }}</td><td class="right">{{ number_format((float)$quotation->subtotal,2) }}</td></tr>
@endif
</tbody>
</table>

<table>
<tr><td class="right">Subtotal</td><td class="right">{{ number_format((float)$quotation->subtotal,2) }}</td></tr>
<tr><td class="right">Discount</td><td class="right">{{ number_format((float)$quotation->discount,2) }}</td></tr>
<tr><td class="right">Tax</td><td class="right">{{ number_format((float)$quotation->tax,2) }}</td></tr>
<tr><td class="right total">Total (LKR)</td><td class="right total">{{ number_format((float)$quotation->total,2) }}</td></tr>
</table>

@if($quotation->notes)
<h3>Notes</h3><p>{!! nl2br(e($quotation->notes)) !!}</p>
@endif
<div class="footer">Thank you for considering Crow.lk.</div>
</body>
</html>
