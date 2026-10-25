<?php
namespace App\Http\Controllers;
use App\Models\Sale; use App\Models\SaleItem; use App\Models\Product; use App\Models\Customer; use App\Models\Expense;
use App\Services\ProfitService; use App\Services\LedgerService;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class ReportController extends Controller {
    public function index(Request $request) {
        $from = $request->get('from', now()->startOfMonth()->toDateString());
        $to = $request->get('to', now()->toDateString());
        $sales = Sale::where('status', '!=', 'cancelled')->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
        $top = SaleItem::select('product_id', DB::raw('SUM(quantity) as qty'))->whereIn('sale_id', (clone $sales)->pluck('id'))->groupBy('product_id')->orderByDesc('qty')->limit(10)->get(); $top->load('product');
        $soldIds = SaleItem::whereIn('sale_id', (clone $sales)->pluck('id'))->pluck('product_id')->unique();
        $slow = Product::whereNotIn('id', $soldIds)->where('is_active', true)->limit(10)->get();
        $credit = Customer::all()->map(fn($c) => ['customer' => $c, 'balance' => LedgerService::customerBalance($c)])->filter(fn($x) => $x['balance'] > 0)->sortByDesc('balance')->values();
        return view('reports.index', [
            'from' => $from, 'to' => $to, 'salesTotal' => (float)(clone $sales)->sum('total'), 'bills' => (clone $sales)->count(),
            'profit' => auth()->user()->isOwner() ? ProfitService::report($from, $to) : null,
            'low' => Product::whereColumn('stock_qty', '<=', 'alert_qty')->where('is_active', true)->get(),
            'top' => $top, 'slow' => $slow, 'credit' => $credit,
        ]);
    }
    public function exportSales() {
        $out = "invoice_no,date,customer,subtotal,discount,total,paid,due,status\n";
        foreach (Sale::with('customer')->orderByDesc('created_at')->get() as $s) $out .= "{$s->invoice_no},{$s->created_at->format('Y-m-d')},\"{$s->customer?->name}\",{$s->subtotal},{$s->discount},{$s->total},{$s->paid_amount},{$s->due_amount},{$s->status}\n";
        return response($out, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="sales.csv"']);
    }
    public function exportCredit() {
        $out = "customer,phone,balance\n";
        foreach (Customer::all() as $c) { $b = LedgerService::customerBalance($c); if ($b > 0) $out .= "\"{$c->name}\",{$c->phone},{$b}\n"; }
        return response($out, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="credit-report.csv"']);
    }
}
