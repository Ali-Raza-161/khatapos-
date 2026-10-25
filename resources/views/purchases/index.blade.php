@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Purchases</h1><p>A purchase increases stock and updates the product purchase price.</p></div></div>
@if(auth()->user()->role!=='cashier')
<div class="card"><h2>New purchase</h2><form method="POST" action="/purchases" id="purForm">@csrf<div class="row">
<div><label>Supplier</label><select name="supplier_id"><option value="">—</option>@foreach($suppliers as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach</select></div>
<div><label>Supplier invoice no.</label><input name="invoice_no"></div><div><label>Date</label><input name="date" type="date" value="{{ date('Y-m-d') }}"></div>
<div><label>Paid now (PKR)</label><input name="paid" type="number" step="0.01" value="0"></div></div>
<div id="purItems" style="margin-top:12px"></div>
<button type="button" class="btn ghost" onclick="addPurRow()">+ Add item</button>
<button class="btn" style="margin-left:8px">Save purchase</button></form>
<script>let pi=0;const prods=@json($products->map(fn($p)=>['id'=>$p->id,'name'=>$p->name]));
function addPurRow(){const d=document.createElement('div');d.className='row';d.style.marginTop='8px';
d.innerHTML=`<div><label>Product</label><select name="items[${pi}][product_id]" required>${prods.map(p=>`<option value="${p.id}">${p.name}</option>`).join('')}</select></div><div><label>Quantity</label><input name="items[${pi}][quantity]" type="number" step="0.01" required></div><div><label>Purchase price (PKR)</label><input name="items[${pi}][purchase_price]" type="number" step="0.01" required></div>`;
document.getElementById('purItems').appendChild(d);pi++;}addPurRow();</script></div>
@endif
<div class="card"><table><tr><th>Date</th><th>Supplier</th><th>Invoice</th><th>Total</th><th>Paid</th><th>Due</th></tr>
@foreach($purchases as $p)<tr><td>{{ $p->created_at->format('Y-m-d') }}</td><td>{{ $p->supplier?->name }}</td><td>{{ $p->invoice_no }}</td><td>{{ pkr($p->total) }}</td><td>{{ pkr($p->paid_amount) }}</td><td>{{ pkr($p->due_amount) }}</td></tr>@endforeach</table>
<div style="margin-top:12px">{{ $purchases->links() }}</div></div>
@endsection
