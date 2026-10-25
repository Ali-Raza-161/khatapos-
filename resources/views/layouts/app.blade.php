<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>KhataPOS — {{ $title ?? 'Shop POS and Ledger' }}</title><link rel="stylesheet" href="/css/app.css"></head>
<body><div class="shell"><aside class="sidebar">
<div class="brand">Khata<span>POS</span></div><div class="tag">{{ shop_name() }} · POS &amp; Ledger</div>
<div class="section">Sales</div>
<a class="nav" href="/dashboard">Dashboard</a><a class="nav" href="/pos">POS Billing</a><a class="nav" href="/products">Products &amp; Stock</a><a class="nav" href="/purchases">Purchases</a>
<div class="section">Ledgers</div>
<a class="nav" href="/customers">Customers</a><a class="nav" href="/suppliers">Suppliers</a><a class="nav" href="/expenses">Expenses</a>
<div class="section">Reports &amp; Help</div>
<a class="nav" href="/reports">Reports</a><a class="nav" href="/reports/day-closing">Day Closing</a><a class="nav" href="/support">Support &amp; Help</a>
@auth @if(auth()->user()->isOwner())<a class="nav" href="/settings">Settings &amp; Users</a><a class="nav" href="/backup">Download Backup</a>@endif @endauth
<div style="margin-top:24px;font-size:13px;color:#94a3b8">Signed in as<br><strong style="color:#fff">{{ auth()->user()->name ?? '' }}</strong> ({{ auth()->user()->role ?? '' }})</div>
<form method="POST" action="/logout" style="margin-top:10px">@csrf<button class="btn ghost small" style="width:100%">Log out</button></form>
</aside><main class="main">
@if(session('ok'))<div class="alert ok">{{ session('ok') }}</div>@endif
@if($errors->any())<div class="alert err">@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
@yield('content')</main></div></body></html>
