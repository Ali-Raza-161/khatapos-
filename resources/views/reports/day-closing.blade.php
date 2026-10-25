@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Day Closing — {{ $date }}</h1><p>Cashier day summary. Print this page for the shop record.</p></div><button class="btn no-print" onclick="window.print()">Print</button></div>
<div class="grid">
<div class="card stat"><div class="label">Sales today</div><div class="value teal">{{ pkr($sales) }}</div><div>{{ $bills }} bills</div></div>
<div class="card stat"><div class="label">Cash received</div><div class="value">{{ pkr($cash) }}</div></div>
<div class="card stat"><div class="label">Credit given</div><div class="value amber">{{ pkr($creditGiven) }}</div></div>
<div class="card stat"><div class="label">Expenses</div><div class="value red">{{ pkr($expenses) }}</div></div>
</div>
@endsection
