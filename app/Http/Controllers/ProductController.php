<?php
namespace App\Http\Controllers;
use App\Models\Product; use App\Models\Category; use App\Models\StockMovement; use App\Models\ActivityLog;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class ProductController extends Controller {
    public function index(Request $request) {
        $q = $request->get('q');
        $products = Product::with('category')->when($q, fn($w) => $w->where('name', 'like', "%$q%")->orWhere('barcode', $q))->orderBy('name')->paginate(50);
        return view('products.index', ['products' => $products, 'categories' => Category::orderBy('name')->get(), 'q' => $q]);
    }
    public function store(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'category_id' => 'nullable|integer', 'barcode' => 'nullable|string', 'unit' => 'required|string', 'purchase_price' => 'required|numeric|min:0', 'sale_price' => 'required|numeric|min:0', 'stock_qty' => 'required|numeric|min:0', 'alert_qty' => 'nullable|numeric|min:0', 'expiry_date' => 'nullable|date']);
        Product::create($data); return back()->with('ok', 'Product added.');
    }
    public function update(Request $request, Product $product) {
        $data = $request->validate(['name' => 'required|string', 'category_id' => 'nullable|integer', 'barcode' => 'nullable|string', 'unit' => 'required|string', 'purchase_price' => 'required|numeric|min:0', 'sale_price' => 'required|numeric|min:0', 'alert_qty' => 'nullable|numeric|min:0', 'expiry_date' => 'nullable|date', 'is_active' => 'nullable|boolean']);
        $product->update($data); return back()->with('ok', 'Product updated.');
    }
    public function adjust(Request $request, Product $product) {
        $data = $request->validate(['quantity' => 'required|numeric', 'reason' => 'required|string']);
        DB::transaction(function () use ($product, $data) {
            $product->increment('stock_qty', $data['quantity']);
            StockMovement::create(['product_id' => $product->id, 'type' => 'adjustment', 'quantity' => $data['quantity'], 'note' => $data['reason']]);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'stock_adjusted', 'detail' => "{$product->name}: {$data['quantity']} ({$data['reason']})"]);
        });
        return back()->with('ok', 'Stock adjusted with reason recorded.');
    }
    public function import(Request $request) {
        $request->validate(['file' => 'required|file']);
        $rows = array_map('str_getcsv', file($request->file('file')->getRealPath()));
        $header = array_map('trim', array_shift($rows)); $count = 0;
        foreach ($rows as $row) {
            if (count($row) < 2) continue;
            $r = array_combine($header, $row);
            $cat = null;
            if (!empty($r['category'])) $cat = Category::firstOrCreate(['name' => trim($r['category'])]);
            Product::updateOrCreate(['barcode' => $r['barcode'] ?? null], [
                'name' => $r['name'], 'category_id' => $cat?->id, 'unit' => $r['unit'] ?? 'piece',
                'purchase_price' => $r['purchase_price'] ?? 0, 'sale_price' => $r['sale_price'] ?? 0, 'stock_qty' => $r['stock_qty'] ?? 0,
            ]);
            $count++;
        }
        return back()->with('ok', "Imported $count products from CSV.");
    }
    public function export() {
        $out = "name,category,barcode,unit,purchase_price,sale_price,stock_qty,alert_qty\n";
        foreach (Product::with('category')->get() as $p) $out .= "\"{$p->name}\",\"{$p->category?->name}\",\"{$p->barcode}\",{$p->unit},{$p->purchase_price},{$p->sale_price},{$p->stock_qty},{$p->alert_qty}\n";
        return response($out, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="products.csv"']);
    }
}
