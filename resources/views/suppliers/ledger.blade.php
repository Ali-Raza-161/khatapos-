@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>{{ $supplier->name }} — Ledger</h1><p>{{ $supplier->phone }} · {{ $supplier->address }}</p></div>
<span class="badge {{ $balance>0?'red':'green' }}" style="font-size:16px;padding:8px 16px">Balance due: {{ pkr($balance) }}</span></div>
<div class="card"><h2>Pay supplier</h2><form method="POST" action="/suppliers/{{ $supplier->id }}/payment">@csrf<div class="row">
<div><label>Amount (PKR)</label><input name="amount" type="number" step="0.01" required></div>
<div><label>Method</label><select name="method"><option value="cash">Cash</option><option value="bank">Bank transfer</option></select></div>
<div><label>Note</label><input name="note"></div></div><button class="btn" style="margin-top:12px">Record payment</button></form></div>
<div class="card"><h2>Purchases</h2><table><tr><th>Date</th><th>Invoice</th><th>Total</th><th>Paid</th><th>Due</th></tr>
@foreach($purchases as $p)<tr><td>{{ $p->created_at->format('Y-m-d') }}</td><td>{{ $p->invoice_no }}</td><td>{{ pkr($p->total) }}</td><td>{{ pkr($p->paid_amount) }}</td><td>{{ pkr($p->due_amount) }}</td></tr>@endforeach</table></div>
<div class="card"><h2>Payments made</h2><table><tr><th>Date</th><th>Amount</th><th>Method</th><th>Note</th></tr>
@foreach($payments as $p)<tr><td>{{ $p->created_at->format('Y-m-d') }}</td><td>{{ pkr($p->amount) }}</td><td>{{ $p->method }}</td><td>{{ $p->note }}</td></tr>@endforeach</table></div>
@endsection
