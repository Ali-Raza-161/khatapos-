@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Return items — {{ $sale->invoice_no }}</h1><p>Returned items go back to stock and reduce the bill total and customer balance.</p></div></div>
<div class="card"><form method="POST" action="/sales/{{ $sale->id }}/return">@csrf<table><tr><th>Item</th><th>Sold qty</th><th>Return qty</th></tr>
@foreach($sale->items as $it)<tr><td>{{ $it->product?->name }}</td><td>{{ $it->quantity }}</td><td><input name="returns[{{ $it->id }}]" type="number" step="0.01" min="0" max="{{ $it->quantity }}" value="0"></td></tr>@endforeach</table>
<button class="btn" style="margin-top:12px">Record return</button></form></div>
@endsection
