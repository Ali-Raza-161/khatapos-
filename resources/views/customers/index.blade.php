@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Customers</h1><p>Every customer has a ledger: bills, payments and a running balance.</p></div></div>
@if(auth()->user()->role!=='cashier')
<div class="card"><h2>Add customer</h2><form method="POST" action="/customers">@csrf<div class="row">
<div><label>Name</label><input name="name" required></div><div><label>Phone</label><input name="phone" placeholder="03xx-xxxxxxx"></div>
<div><label>Address</label><input name="address"></div><div><label>Credit limit (PKR)</label><input name="credit_limit" type="number" step="0.01" value="0"></div>
<div><label>Opening balance (PKR)</label><input name="opening_balance" type="number" step="0.01" value="0"></div></div><button class="btn" style="margin-top:12px">Add customer</button></form></div>
@endif
<div class="card"><table><tr><th>Customer</th><th>Phone</th><th>Credit limit</th><th>Balance due</th><th></th></tr>
@foreach($rows as $r)<tr><td><strong>{{ $r['customer']->name }}</strong></td><td>{{ $r['customer']->phone }}</td><td>{{ pkr($r['customer']->credit_limit) }}</td>
<td>@if($r['balance']>0)<span class="badge red">{{ pkr($r['balance']) }}</span>@else<span class="badge green">{{ pkr($r['balance']) }}</span>@endif</td>
<td><a href="/customers/{{ $r['customer']->id }}/ledger">View ledger</a></td></tr>@endforeach</table></div>
@endsection
