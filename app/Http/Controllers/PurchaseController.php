<?php
namespace App\Http\Controllers;
use App\Models\Purchase; use App\Models\PurchaseItem; use App\Models\Product; use App\Models\Supplier; use App\Models\Payment; use App\Models\StockMovement; use App\Models\ActivityLog;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class PurchaseController extends Controller {
    public function index() { return view('purchases.index', ['purchases' => Purchase::with('supplier')->orderByDesc('created_at')->paginate(50), 'suppliers' => Supplier::orderBy('name')->get(), 'products' => Product::orderBy('name')->get()]); }
    public function store(Request $request) {
        $data = $request->validate(['supplier_id' => 'nullable|integer', 'invoice_no' => 'nullable|string', 'date' => 'nullable|date', 'paid' => 'nullable|numeric|min:0', 'items' => 'required|array|min:1', 'items.*.product_id' => 'required|integer', 'items.*.quantity' => 'required|numeric|min:0.01', 'items.*.purchase_price' => 'required|numeric|min:0']);
        DB::transaction(function () use ($data) {
            $total = 0;
            foreach ($data['items'] as $it) $total += $it['quantity'] * $it['purchase_price'];
            $paid = (float)($data['paid'] ?? 0);
            $pur = Purchase::create(['supplier_id' => $data['supplier_id'] ?? null, 'invoice_no' => $data['invoice_no'] ?? null, 'total' => $total, 'paid_amount' => $paid, 'due_amount' => $total - $paid, 'date' => $data['date'] ?? now()->toDateString()]);
            foreach ($data['items'] as $it) {
                PurchaseItem::create(['purchase_id' => $pur->id, 'product_id' => $it['product_id'], 'quantity' => $it['quantity'], 'purchase_price' => $it['purchase_price'], 'line_total' => $it['quantity'] * $it['purchase_price']]);
                $p = Product::lockForUpdate()->find($it['product_id']);
                $p->increment('stock_qty', $it['quantity']); $p->update(['purchase_price' => $it['purchase_price']]);
                StockMovement::create(['product_id' => $p->id, 'type' => 'purchase', 'quantity' => $it['quantity'], 'reference_type' => 'purchase', 'reference_id' => $pur->id]);
            }
            if ($paid > 0 && $pur->supplier_id) Payment::create(['type' => 'supplier', 'party_id' => $pur->supplier_id, 'related_type' => 'purchase', 'related_id' => $pur->id, 'amount' => $paid, 'method' => 'cash', 'user_id' => auth()->id()]);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'purchase_created', 'detail' => "Purchase #{$pur->id} total PKR {$total}"]);
        });
        return back()->with('ok', 'Purchase recorded. Stock increased and purchase price updated.');
    }
}
