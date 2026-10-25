<?php
namespace App\Http\Controllers;
use App\Models\Product; use App\Models\Customer; use App\Models\Sale; use App\Models\HeldBill; use App\Models\ActivityLog;
use App\Services\BillingService;
use Illuminate\Http\Request;
class PosController extends Controller {
    public function index() { return view('pos', ['customers' => Customer::orderBy('name')->get(), 'held' => HeldBill::orderByDesc('created_at')->get()]); }
    public function search(Request $request) {
        $q = trim($request->get('q', ''));
        $products = Product::with('category')->where('is_active', true)
            ->where(function ($w) use ($q) { $w->where('name', 'like', "%$q%")->orWhere('barcode', $q); })
            ->limit(20)->get();
        return response()->json($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'barcode' => $p->barcode, 'unit' => $p->unit, 'price' => (float)$p->sale_price, 'stock' => (float)$p->stock_qty, 'category' => $p->category?->name]));
    }
    public function checkout(Request $request) {
        $data = $request->validate([
            'items' => 'required|array|min:1', 'items.*.product_id' => 'required|integer', 'items.*.quantity' => 'required|numeric|min:0.01',
            'discount' => 'nullable|numeric|min:0', 'paid' => 'nullable|numeric|min:0', 'customer_id' => 'nullable|integer', 'method' => 'nullable|string',
        ]);
        try {
            $sale = BillingService::checkout($data['items'], (float)($data['discount'] ?? 0), (float)($data['paid'] ?? 0), $data['customer_id'] ?? null, auth()->id(), $data['method'] ?? 'cash');
        } catch (\Exception $e) { return response()->json(['error' => $e->getMessage()], 422); }
        return response()->json(['sale_id' => $sale->id, 'invoice_no' => $sale->invoice_no, 'total' => (float)$sale->total, 'due' => (float)$sale->due_amount, 'receipt_url' => route('sales.receipt', $sale)]);
    }
    public function hold(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'items_json' => 'required|string', 'discount' => 'nullable|numeric|min:0']);
        HeldBill::create(['user_id' => auth()->id(), 'name' => $data['name'], 'items_json' => $data['items_json'], 'discount' => $data['discount'] ?? 0]);
        return response()->json(['ok' => true]);
    }
    public function resume(HeldBill $bill) { $data = $bill->toArray(); $bill->delete(); return response()->json($data); }
    public function receipt(Sale $sale) { $sale->load('items.product', 'customer'); return view('sales.receipt', ['sale' => $sale, 'size' => request('size', 'a4')]); }
    public function returnForm(Sale $sale) { $sale->load('items.product'); return view('sales.return', ['sale' => $sale]); }
    public function doReturn(Request $request, Sale $sale) {
        $data = $request->validate(['returns' => 'required|array']);
        try { BillingService::returnItems($sale, array_filter($data['returns'], fn($q) => $q > 0), auth()->id()); }
        catch (\Exception $e) { return back()->withErrors(['returns' => $e->getMessage()]); }
        return redirect()->route('sales.receipt', $sale)->with('ok', 'Return recorded. Stock and ledger updated.');
    }
    public function cancel(Request $request, Sale $sale) {
        $request->validate(['reason' => 'required|string']);
        try { BillingService::cancel($sale, $request->reason, auth()->id()); }
        catch (\Exception $e) { return back()->withErrors(['reason' => $e->getMessage()]); }
        return redirect('/dashboard')->with('ok', 'Bill cancelled with reason. Stock restored; the bill was not deleted.');
    }
}
