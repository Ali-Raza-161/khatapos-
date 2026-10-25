@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Dashboard</h1><p>Today at a glance for {{ shop_name() }}.</p></div><a class="btn" href="/pos">+ New Bill</a></div>
<div class="grid">
<div class="card stat"><div class="label">Sales today</div><div class="value teal">{{ pkr($salesToday) }}</div><div>{{ $billsToday }} bills</div></div>
<div class="card stat"><div class="label">Cash received today</div><div class="value">{{ pkr($cashToday) }}</div></div>
<div class="card stat"><div class="label">Credit given today</div><div class="value amber">{{ pkr($creditToday) }}</div></div>
<div class="card stat"><div class="label">Expenses today</div><div class="value red">{{ pkr($expToday) }}</div></div>
@if($profit)<div class="card stat"><div class="label">Net profit today (owner only)</div><div class="value teal">{{ pkr($profit['net']) }}</div><div>Gross {{ pkr($profit['gross']) }} · Expenses {{ pkr($profit['expenses']) }}</div></div>@endif
</div>
<div class="card"><h2>Low stock alerts</h2>
@if($low->isEmpty())<p>All products are above their alert level.</p>@else<table><tr><th>Product</th><th>Stock</th><th>Alert level</th></tr>
@foreach($low as $p)<tr><td>{{ $p->name }}</td><td><span class="badge red">{{ $p->stock_qty }} {{ $p->unit }}</span></td><td>{{ $p->alert_qty }}</td></tr>@endforeach</table>@endif</div>
<div class="card"><h2>Customers with pending credit</h2>
@if($credit->isEmpty())<p>No pending customer credit.</p>@else<table><tr><th>Customer</th><th>Balance</th><th></th></tr>
@foreach($credit as $r)<tr><td>{{ $r['customer']->name }}</td><td><strong>{{ pkr($r['balance']) }}</strong></td><td><a href="/customers/{{ $r['customer']->id }}/ledger">View ledger</a></td></tr>@endforeach</table>@endif</div>
<div class="card"><h2>Top selling products</h2>
@if($top->isEmpty())<p>No sales yet.</p>@else<table><tr><th>Product</th><th>Quantity sold</th></tr>
@foreach($top as $t)<tr><td>{{ $t->product?->name }}</td><td>{{ $t->qty }}</td></tr>@endforeach</table>@endif</div>
@endsection
