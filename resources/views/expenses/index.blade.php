@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Expenses</h1><p>Monthly summary by category. Expenses reduce net profit.</p></div></div>
@if(auth()->user()->role!=='cashier')
<div class="card"><h2>Record expense</h2><form method="POST" action="/expenses">@csrf<div class="row">
<div><label>Category</label><select name="category"><option>rent</option><option>electricity</option><option>salary</option><option>transport</option><option>other</option></select></div>
<div><label>Amount (PKR)</label><input name="amount" type="number" step="0.01" required></div>
<div><label>Date</label><input name="expense_date" type="date" value="{{ date('Y-m-d') }}" required></div>
<div><label>Description</label><input name="description"></div></div><button class="btn" style="margin-top:12px">Save expense</button></form></div>
@endif
<div class="card"><form method="GET" style="display:flex;gap:10px"><input name="month" type="month" value="{{ $month }}"><button class="btn">Filter</button></form>
<p style="font-size:20px"><strong>Total for {{ $month }}: {{ pkr($total) }}</strong></p>
@foreach($byCat as $cat=>$amt)<div>{{ ucfirst($cat) }}: {{ pkr($amt) }}</div>@endforeach
<table style="margin-top:12px"><tr><th>Date</th><th>Category</th><th>Amount</th><th>Description</th></tr>
@foreach($expenses as $e)<tr><td>{{ $e->expense_date }}</td><td>{{ $e->category }}</td><td>{{ pkr($e->amount) }}</td><td>{{ $e->description }}</td></tr>@endforeach</table></div>
@endsection
