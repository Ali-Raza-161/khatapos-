@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Reports</h1><p>Filter by date. Profit figures are visible to the owner only.</p></div>
<div><a class="btn ghost" href="/reports/export-sales">Export sales CSV</a> <a class="btn ghost" href="/reports/export-credit">Export credit CSV</a></div></div>
<div class="card"><form method="GET" class="row"><div><label>From</label><input name="from" type="date" value="{{ $from }}"></div><div><label>To</label><input name="to" type="date" value="{{ $to }}"></div><div style="align-self:end"><button class="btn">Filter</button></div></form></div>
<div class="grid">
<div class="card stat"><div class="label">Sales in period</div><div class="value teal">{{ pkr($salesTotal) }}</div><div>{{ $bills }} bills</div></div>
@if($profit)<div class="card stat"><div class="label">Gross profit</div><div class="value">{{ pkr($profit['gross']) }}</div><div>Cost of goods {{ pkr($profit['cogs']) }}</div></div>
<div class="card stat"><div class="label">Net profit</div><div class="value teal">{{ pkr($profit['net']) }}</div><div>After expenses {{ pkr($profit['expenses']) }}</div></div>
@else<div class="card stat"><div class="label">Profit</div><div>Visible to the owner only.</div></div>@endif
</div>
<div class="card"><h2>Top selling products</h2><table><tr><th>Product</th><th>Quantity sold</th></tr>
@foreach($top as $t)<tr><td>{{ $t->product?->name }}</td><td>{{ $t->qty }}</td></tr>@endforeach</table></div>
<div class="card"><h2>Slow moving products (no sales in period)</h2>
@if($slow->isEmpty())<p>None.</p>@else<table><tr><th>Product</th><th>Stock</th></tr>@foreach($slow as $p)<tr><td>{{ $p->name }}</td><td>{{ $p->stock_qty }}</td></tr>@endforeach</table>@endif</div>
<div class="card"><h2>Low stock</h2>
@if($low->isEmpty())<p>None.</p>@else<table><tr><th>Product</th><th>Stock</th><th>Alert level</th></tr>@foreach($low as $p)<tr><td>{{ $p->name }}</td><td>{{ $p->stock_qty }}</td><td>{{ $p->alert_qty }}</td></tr>@endforeach</table>@endif</div>
<div class="card"><h2>Customer credit report</h2><table><tr><th>Customer</th><th>Balance due</th><th></th></tr>
@foreach($credit as $r)<tr><td>{{ $r['customer']->name }}</td><td>{{ pkr($r['balance']) }}</td><td><a href="/customers/{{ $r['customer']->id }}/ledger">Ledger</a></td></tr>@endforeach</table></div>
<div class="card"><p>PDF export: use the browser print option on any report or receipt page and choose “Save as PDF”. Thermal receipts use the 80mm layout below.</p>
<p><a href="/reports/day-closing">View day closing summary</a></p></div>
@endsection
