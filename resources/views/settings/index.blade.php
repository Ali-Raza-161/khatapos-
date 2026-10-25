@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Settings &amp; Users</h1><p>Owner only. Shop details appear on receipts and the support contact card.</p></div></div>
<div class="card"><h2>Shop details</h2><form method="POST" action="/settings">@csrf<div class="row">
<div><label>Shop name</label><input name="shop_name" value="{{ setting('shop_name') }}"></div>
<div><label>Shop phone</label><input name="shop_phone" value="{{ setting('shop_phone') }}"></div>
<div><label>Shop email (support contact)</label><input name="shop_email" type="email" value="{{ setting('shop_email') }}"></div>
<div><label>Receipt footer</label><input name="receipt_footer" value="{{ setting('receipt_footer') }}"></div>
<div><label>Printer size</label><select name="printer_size"><option value="a4" {{ setting('printer_size')==='a4'?'selected':'' }}>A4</option><option value="thermal" {{ setting('printer_size')==='thermal'?'selected':'' }}>80mm thermal</option></select></div></div>
<button class="btn" style="margin-top:12px">Save settings</button></form>
<p style="color:#64748b;font-size:14px">Leave the support email or phone empty if you do not want it shown — no contact details are invented by the app.</p></div>
<div class="card"><h2>Add user</h2><form method="POST" action="/settings/users">@csrf<div class="row">
<div><label>Name</label><input name="name" required></div><div><label>Email</label><input name="email" type="email" required></div>
<div><label>Password</label><input name="password" type="password" required></div>
<div><label>Role</label><select name="role"><option value="owner">Owner</option><option value="manager">Manager</option><option value="cashier">Cashier</option></select></div></div>
<button class="btn" style="margin-top:12px">Create user</button></form>
<table style="margin-top:16px"><tr><th>Name</th><th>Email</th><th>Role</th></tr>
@foreach($users as $u)<tr><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->role }}</td></tr>@endforeach</table></div>
<div class="card"><h2>Activity log</h2><table><tr><th>Time</th><th>User</th><th>Action</th><th>Detail</th></tr>
@foreach($logs as $l)<tr><td>{{ $l->created_at }}</td><td>{{ $l->user_id }}</td><td>{{ $l->action }}</td><td>{{ $l->detail }}</td></tr>@endforeach</table></div>
@endsection
