@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>{{ $customer->name }} — Ledger</h1><p>{{ $customer->phone }} · {{ $customer->address }} · Credit limit {{ pkr($customer->credit_limit) }}</p></div>
<div><span class="badge {{ $balance>0?'red':'green' }}" style="font-size:16px;padding:8px 16px">Balance due: {{ pkr($balance) }}</span>
@if($whatsapp)<a class="btn amber" style="margin-left:8px" href="{{ $whatsapp }}" target="_blank">Send reminder on WhatsApp</a>@endif</div></div>
<div class="card"><h2>Receive payment</h2><form method="POST" action="/customers/{{ $customer->id }}/payment">@csrf<div class="row">
<div><label>Amount (PKR)</label><input name="amount" type="number" step="0.01" required></div>
<div><label>Method</label><select name="method"><option value="cash">Cash</option><option value="bank">Bank transfer</option><option value="card">Card</option></select></div>
<div><label>Note</label><input name="note"></div></div><button class="btn" style="margin-top:12px">Record payment &amp; print slip</button></form>
<p style="color:#64748b;font-size:14px">After recording, use the browser print option to print a payment slip.</p></div>
<div class="card"><h2>Ledger timeline</h2><table><tr><th>Date</th><th>Description</th><th>Bill (debit)</th><th>Payment (credit)</th><th>Running balance</th></tr>
@foreach($timeline as $t)<tr><td>{{ $t['date'] }}</td><td>{{ $t['type'] }}</td><td>{{ $t['debit']?pkr($t['debit']):'—' }}</td><td>{{ $t['credit']?pkr($t['credit']):'—' }}</td><td><strong>{{ pkr($t['balance']) }}</strong></td></tr>@endforeach</table></div>
@endsection
