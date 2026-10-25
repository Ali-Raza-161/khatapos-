<?php
namespace App\Http\Controllers;
use App\Models\Sale; use App\Models\Product; use App\Models\Customer; use App\Models\Payment; use App\Models\Expense; use App\Models\SaleItem;
use App\Services\LedgerService; use App\Services\ProfitService;
use Illuminate\Support\Facades\DB;
class DashboardController extends Controller {
    public function index() {
        $today = now()->toDateString();
        $salesToday = Sale::whereDate('created_at', $today)->where('status', '!=', 'cancelled');
        $cashToday = Payment::whereDate('created_at', $today)->where('method', 'cash')->sum('amount');
        $expToday = Expense::whereDate('expense_date', $today)->sum('amount');
        $low = Product::whereColumn('stock_qty', '<=', 'alert_qty')->where('is_active', true)->orderBy('stock_qty')->limit(10)->get();
        $credit = Customer::all()->map(fn($c) => ['customer' => $c, 'balance' => LedgerService::customerBalance($c)])
            ->filter(fn($x) => $x['balance'] > 0)->sortByDesc('balance')->take(5)->values();
        $top = SaleItem::select('product_id', DB::raw('SUM(quantity) as qty'))->groupBy('product_id')->orderByDesc('qty')->limit(5)->get();
        $top->load('product');
        return view('dashboard', [
            'salesToday' => (float)(clone $salesToday)->sum('total'),
            'billsToday' => (clone $salesToday)->count(),
            'cashToday' => (float)$cashToday, 'expToday' => (float)$expToday,
            'creditToday' => (float)(clone $salesToday)->sum('due_amount'),
            'low' => $low, 'credit' => $credit, 'top' => $top,
            'profit' => auth()->user()->isOwner() ? ProfitService::report($today, $today) : null,
        ]);
    }
    public function dayClosing() {
        $today = now()->toDateString();
        $sales = Sale::whereDate('created_at', $today)->where('status', '!=', 'cancelled');
        return view('reports.day-closing', [
            'date' => $today, 'sales' => (float)(clone $sales)->sum('total'), 'bills' => (clone $sales)->count(),
            'cash' => (float)Payment::whereDate('created_at', $today)->where('method', 'cash')->sum('amount'),
            'creditGiven' => (float)(clone $sales)->sum('due_amount'),
            'expenses' => (float)Expense::whereDate('expense_date', $today)->sum('amount'),
        ]);
    }
}
