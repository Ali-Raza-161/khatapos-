@extends('layouts.app')
@section('content')
<div class="topbar"><div><h1>POS Billing</h1><p>Search by product name or scan a barcode — a barcode scanner works like a keyboard.</p></div></div>
<div class="pos"><div>
<div class="card"><label>Search product / barcode</label><input id="search" class="searchbox" placeholder="Type a name or scan a barcode, then press Enter" autofocus>
<div id="results" style="margin-top:10px"></div></div>
<div class="card"><h2>Bill items</h2><div id="cart"></div>
<div style="display:flex;gap:10px;margin-top:14px"><input id="discount" type="number" min="0" step="0.01" placeholder="Whole-bill discount (PKR)" style="flex:1">
<button class="btn ghost" onclick="holdBill()">Hold bill</button></div>
<div style="margin-top:10px"><strong>Held bills:</strong>
@foreach($held as $h)<button class="btn ghost small" onclick="resumeBill({{ $h->id }})">{{ $h->name }}</button> @endforeach</div>
</div></div>
<div><div class="card"><h2>Payment</h2>
<label>Customer (required for partial or full credit)</label>
<select id="customer"><option value="">Walk-in customer (cash only)</option>@foreach($customers as $c)<option value="{{ $c->id }}">{{ $c->name }} — {{ $c->phone }}</option>@endforeach</select>
<label style="margin-top:12px">Payment method</label><select id="method"><option value="cash">Cash</option><option value="bank">Bank transfer</option><option value="card">Card</option></select>
<label style="margin-top:12px">Amount paid now (PKR)</label><input id="paid" type="number" min="0" step="0.01" value="0">
<div id="totals" style="margin:16px 0;font-size:18px"></div>
<button class="btn" style="width:100%;padding:14px;font-size:17px" onclick="checkout()">Complete bill &amp; print receipt</button>
<div id="msg" style="margin-top:12px"></div>
<div class="notice" style="margin-top:16px">Cash: pay the full amount. Partial: pay part now, the rest goes to the customer ledger. Full credit: pay 0 and select a customer.</div>
</div></div></div>
<script>
let cart=[];
const fmt=n=>'PKR '+Number(n).toLocaleString();
async function search(q){ if(q.length<1){document.getElementById('results').innerHTML='';return;}
 const r=await fetch('/pos/search?q='+encodeURIComponent(q)); const items=await r.json();
 document.getElementById('results').innerHTML=items.map(p=>`<div class="cart-line"><span><strong>${p.name}</strong> <small>${p.category||''} · ${p.barcode||''} · stock ${p.stock}</small></span><button class="btn small" onclick='addItem(${JSON.stringify(p)})'>Add — ${fmt(p.price)}</button></div>`).join('')||'<p>No product found.</p>';}
function addItem(p){ let l=cart.find(x=>x.product_id===p.id); if(l) l.quantity++; else cart.push({product_id:p.id,name:p.name,price:p.price,quantity:1}); render(); }
function render(){ let sub=cart.reduce((s,x)=>s+x.price*x.quantity,0); let disc=parseFloat(document.getElementById('discount').value||0);
 document.getElementById('cart').innerHTML=cart.length?cart.map((x,i)=>`<div class="cart-line"><span>${x.name} × <input type="number" min="0.01" step="0.01" value="${x.quantity}" style="width:70px" onchange="cart[${i}].quantity=parseFloat(this.value);render()"> @ ${fmt(x.price)}</span><span>${fmt(x.price*x.quantity)} <button class="btn ghost small" onclick="cart.splice(${i},1);render()">Remove</button></span></div>`).join(''):'<p>No items yet. Search above and add products.</p>';
 document.getElementById('totals').innerHTML=`Subtotal: ${fmt(sub)}<br>Discount: ${fmt(disc)}<br><strong>Total: ${fmt(Math.max(0,sub-disc))}</strong>`; }
document.getElementById('search').addEventListener('input',e=>search(e.target.value));
document.getElementById('search').addEventListener('keydown',async e=>{ if(e.key==='Enter'){ const r=await fetch('/pos/search?q='+encodeURIComponent(e.target.value)); const items=await r.json(); if(items.length===1){addItem(items[0]); e.target.value=''; document.getElementById('results').innerHTML='';} }});
document.getElementById('discount').addEventListener('input',render);
async function checkout(){ const disc=parseFloat(document.getElementById('discount').value||0); const paid=parseFloat(document.getElementById('paid').value||0);
 const cust=document.getElementById('customer').value;
 const r=await fetch('/pos/checkout',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({items:cart,discount:disc,paid:paid,customer_id:cust||null,method:document.getElementById('method').value})});
 const d=await r.json(); if(d.error){document.getElementById('msg').innerHTML=`<div class="alert err">${d.error}</div>`;return;}
 document.getElementById('msg').innerHTML=`<div class="alert ok">Bill ${d.invoice_no} completed. Total ${fmt(d.total)}, balance due ${fmt(d.due)}. <a href="${d.receipt_url}" target="_blank">Open receipt</a></div>`; cart=[]; render(); }
async function holdBill(){ const name=prompt('Name for this held bill:'); if(!name) return;
 await fetch('/pos/hold',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},body:JSON.stringify({name:name,items_json:JSON.stringify(cart),discount:document.getElementById('discount').value||0})}); location.reload(); }
async function resumeBill(id){ const r=await fetch('/pos/held/'+id+'/resume'); const d=await r.json(); cart=JSON.parse(d.items_json).map(x=>({product_id:x.product_id,name:x.name,price:x.price,quantity:x.quantity})); document.getElementById('discount').value=d.discount; render(); }
render();
</script>
@endsection
