@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>Support &amp; Help</h1><p>Guides, answers, and a ticket form — for every role.</p></div></div>
<div class="grid">
<div class="card"><h2>Contact support</h2>
@if(setting('shop_email'))<p><strong>Email:</strong> {{ setting('shop_email') }}</p>@else<p><strong>Email:</strong> Not set yet — add it in Settings.</p>@endif
@if(setting('shop_phone'))<p><strong>Phone / WhatsApp:</strong> <a href="https://wa.me/{{ preg_replace('/\D/','',setting('shop_phone')) }}" target="_blank">{{ setting('shop_phone') }}</a></p>
@else<p><strong>Phone / WhatsApp:</strong> Not set yet — add it in Settings.</p>@endif
<p style="color:#64748b;font-size:14px">No support contact is shown unless the shop owner enters it in Settings.</p></div>
<div class="card"><h2>Help Center — quick guides</h2>
<h3>Using the POS</h3><p>Open POS Billing, search by product name or scan a barcode, set quantities, add a discount, then enter the amount paid. Cash means full payment; partial payment needs a customer; paying 0 with a customer creates a full credit bill.</p>
<h3>Customer ledgers</h3><p>Open Customers, then View ledger. Bills increase the balance, payments reduce it. The WhatsApp button opens a ready reminder message — nothing is sent automatically.</p>
<h3>Stock</h3><p>Products below their alert level appear in red on the dashboard and Products page. Adjust stock only with a reason (damage, correction); the reason is logged.</p>
<h3>Reports</h3><p>Reports show sales, top and slow moving products, low stock and customer credit. Profit is visible to the owner only. Export CSV from the Reports page; for PDF, print any page and choose “Save as PDF”.</p></div>
</div>
<div class="card"><h2>Frequently asked questions</h2>
<p><strong>How do I make a credit bill?</strong> Select a customer on the POS screen and enter 0 (full credit) or a smaller amount (partial) as paid. The rest stays on the customer ledger.</p>
<p><strong>How do I record a return?</strong> Open the bill receipt and use the Return page. Returned items go back to stock and the bill total and customer balance are reduced. Bills are never deleted.</p>
<p><strong>Why is a product shown in red?</strong> Its stock is at or below its alert level. Buy more stock from Purchases, or adjust stock with a reason.</p>
<p><strong>How do I back up the data?</strong> The owner can use Download Backup in the sidebar (SQLite demo database). For MySQL, use mysqldump — see the README.</p>
<p><strong>How do I print a receipt?</strong> After checkout, open the receipt and print it. Choose A4 or the 80mm thermal layout. Set the default size in Settings.</p>
<p><strong>Can a cashier see profit?</strong> No. Profit is shown to the owner only; managers see reports without profit.</p></div>
<div class="card"><h2>Open a support ticket</h2><form method="POST" action="/support">@csrf<div class="row">
<div><label>Your name</label><input name="name" value="{{ auth()->user()->name }}" required></div>
<div><label>Subject</label><input name="subject" required></div>
<div><label>Priority</label><select name="priority"><option value="low">Low</option><option value="normal" selected>Normal</option><option value="high">High</option></select></div></div>
<div style="margin-top:12px"><label>Message</label><textarea name="message" rows="4" required></textarea></div>
<button class="btn" style="margin-top:12px">Submit ticket</button></form></div>
<div class="card"><h2>Tickets</h2><table><tr><th>#</th><th>Subject</th><th>From</th><th>Priority</th><th>Status</th><th>Reply</th>@if(auth()->user()->role!=='cashier')<th>Update</th>@endif</tr>
@foreach($tickets as $t)<tr><td>{{ $t->id }}</td><td><strong>{{ $t->subject }}</strong><br><small>{{ $t->message }}</small></td><td>{{ $t->name }}</td><td>{{ $t->priority }}</td>
<td><span class="badge {{ $t->status==='resolved'?'green':($t->status==='in_progress'?'amber':'red') }}">{{ $t->status }}</span></td><td>{{ $t->reply_note }}</td>
@if(auth()->user()->role!=='cashier')<td><form method="POST" action="/support/{{ $t->id }}" style="display:flex;gap:6px">@csrf<select name="status"><option value="open" {{ $t->status==='open'?'selected':'' }}>Open</option><option value="in_progress" {{ $t->status==='in_progress'?'selected':'' }}>In Progress</option><option value="resolved" {{ $t->status==='resolved'?'selected':'' }}>Resolved</option></select><input name="reply_note" placeholder="Reply note" value="{{ $t->reply_note }}"><button class="btn small">Update</button></form></td>@endif</tr>@endforeach</table></div>
@endsection
