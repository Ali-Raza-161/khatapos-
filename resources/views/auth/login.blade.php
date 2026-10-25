<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>KhataPOS — Sign in</title><link rel="stylesheet" href="/css/app.css"></head><body>
<div class="login-wrap"><div class="card"><h1>KhataPOS</h1><p style="color:#64748b">Point of sale and ledger system for local shops.</p>
<div class="notice"><strong>Demo account (demo only):</strong><br>Email: demo@khatapos.local<br>Password: demo123<br><small>Create your own owner account from Settings, then remove the demo account.</small></div>
@if($errors->any())<div class="alert err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
<form method="POST" action="/login">@csrf
<div style="margin-bottom:12px"><label>Email</label><input name="email" type="email" value="{{ old('email') }}" required autofocus></div>
<div style="margin-bottom:16px"><label>Password</label><input name="password" type="password" required></div>
<button class="btn" style="width:100%">Sign in</button></form></div></div></body></html>
