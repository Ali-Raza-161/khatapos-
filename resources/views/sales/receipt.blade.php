@extends('layouts.app')
@section('content')
<div class="{{ $size==='thermal'?'receipt-thermal':'receipt-a4' }}">
<div class="no-print" style="margin-bottom:16px"><a class="btn" href="/sales/{{ $sale->id }}/receipt?size=a4">A4 receipt</a> <a class="btn ghost" href="/sales/{{ $sale->id }}/receipt?size=thermal">80mm thermal</a> <button class="btn amber" onclick="window.print()">Print</button> <a class="btn ghost" href="/sales/{{ $sale->id }}/return">Return items</a></div>
<h1 style="text-align:center">{{ shop_name() }}</h1><p style="text-align:center">{{ setting('shop_phone') }}<br>{{ setting('receipt_footer') }}</p>
<p>Invoice: <strong>{{ $sale->invoice_no }}</strong><br>Date: {{ $sale->created_at }}<br>Customer: {{ $sale->customer?->name ?? 'Walk-in' }}<br>Status: {{ $sale->status }}</p>
<table><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr>
@foreach($sale->items as $it)<tr><td>{{ $it->product?->name }}</td><td>{{ $it->quantity }}</td><td>{{ pkr($it->sale_price) }}</td><td>{{ pkr($it->line_total) }}</td></tr>@endforeach</table>
<p style="text-align:right">Subtotal: {{ pkr($sale->subtotal) }}<br>Discount: {{ pkr($sale->discount) }}<br><strong>Total: {{ pkr($sale->total) }}</strong><br>Paid: {{ pkr($sale->paid_amount) }}<br><strong>Balance due: {{ pkr($sale->due_amount) }}</strong></p>
<p style="text-align:center">Thank you for shopping with us!</p>
@if($sale->status==='cancelled')<p style="text-align:center;color:#dc2626"><strong>Cancelled:</strong> {{ $sale->cancel_reason }}</p>@endif
@if($sale->status!=='cancelled')<div class="card no-print"><h2>Cancel this bill (owner / manager)</h2><form method="POST" action="/sales/{{ $sale->id }}/cancel">@csrf<label>Reason</label><input name="reason" required><button class="btn" style="margin-top:8px">Cancel bill (stock will be restored)</button></form></div>@endif
</div>
@endsection
