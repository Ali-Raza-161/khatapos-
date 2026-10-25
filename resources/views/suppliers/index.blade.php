@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Suppliers</h1><p>Supplier ledgers: purchases minus payments made.</p></div></div>
@if(auth()->user()->role!=='cashier')
<div class="card"><h2>Add supplier</h2><form method="POST" action="/suppliers">@csrf<div class="row">
<div><label>Name</label><input name="name" required></div><div><label>Phone</label><input name="phone"></div>
<div><label>Address</label><input name="address"></div><div><label>Opening balance (PKR)</label><input name="opening_balance" type="number" step="0.01" value="0"></div></div><button class="btn" style="margin-top:12px">Add supplier</button></form></div>
@endif
<div class="card"><table><tr><th>Supplier</th><th>Phone</th><th>Balance due</th><th></th></tr>
@foreach($rows as $r)<tr><td><strong>{{ $r['supplier']->name }}</strong></td><td>{{ $r['supplier']->phone }}</td>
<td>@if($r['balance']>0)<span class="badge red">{{ pkr($r['balance']) }}</span>@else<span class="badge green">{{ pkr($r['balance']) }}</span>@endif</td>
<td><a href="/suppliers/{{ $r['supplier']->id }}/ledger">View ledger</a></td></tr>@endforeach</table></div>
@endsection
