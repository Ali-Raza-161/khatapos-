@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Products &amp; Stock</h1><p>Red means low stock. Adjust stock only with a reason — it is recorded in the activity log.</p></div><a class="btn ghost" href="/products/export">Export CSV</a></div>
@if(auth()->user()->role!=='cashier')
<div class="card"><h2>Add product</h2><form method="POST" action="/products">@csrf<div class="row">
<div><label>Name</label><input name="name" required></div><div><label>Category</label><select name="category_id"><option value="">—</option>@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div><label>Barcode</label><input name="barcode"></div><div><label>Unit</label><select name="unit"><option>piece</option><option>kg</option><option>dozen</option><option>packet</option><option>litre</option></select></div>
<div><label>Purchase price (PKR)</label><input name="purchase_price" type="number" step="0.01" required></div><div><label>Sale price (PKR)</label><input name="sale_price" type="number" step="0.01" required></div>
<div><label>Opening stock</label><input name="stock_qty" type="number" step="0.01" required></div><div><label>Low stock alert level</label><input name="alert_qty" type="number" step="0.01" value="5"></div>
<div><label>Expiry date (optional)</label><input name="expiry_date" type="date"></div></div><button class="btn" style="margin-top:12px">Add product</button></form>
<form method="POST" action="/products/import" enctype="multipart/form-data" style="margin-top:16px">@csrf<label>Import products from CSV (columns: name, category, barcode, unit, purchase_price, sale_price, stock_qty)</label><input type="file" name="file" accept=".csv" required><button class="btn ghost" style="margin-top:8px">Import CSV</button></form></div>
@endif
<div class="card"><form method="GET" style="display:flex;gap:10px"><input name="q" value="{{ $q }}" placeholder="Search by name or barcode"><button class="btn">Search</button></form>
<table style="margin-top:12px"><tr><th>Product</th><th>Category</th><th>Barcode</th><th>Purchase</th><th>Sale</th><th>Stock</th><th></th></tr>
@foreach($products as $p)<tr><td><strong>{{ $p->name }}</strong><br><small>{{ $p->unit }}</small></td><td>{{ $p->category?->name }}</td><td>{{ $p->barcode }}</td><td>{{ pkr($p->purchase_price) }}</td><td>{{ pkr($p->sale_price) }}</td>
<td>@if($p->is_low)<span class="badge red">{{ $p->stock_qty }} — LOW</span>@else<span class="badge green">{{ $p->stock_qty }}</span>@endif</td>
<td>@if(auth()->user()->role!=='cashier')<form method="POST" action="/products/{{ $p->id }}/adjust" style="display:flex;gap:6px">@csrf<input name="quantity" type="number" step="0.01" placeholder="+/- qty" style="width:90px" required><input name="reason" placeholder="Reason (damage, correction)" required><button class="btn small">Adjust</button></form>@endif</td></tr>@endforeach</table>
<div style="margin-top:12px">{{ $products->links() }}</div></div>
@endsection
