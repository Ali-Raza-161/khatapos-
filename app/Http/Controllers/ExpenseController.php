<?php
namespace App\Http\Controllers;
use App\Models\Expense;
use Illuminate\Http\Request;
class ExpenseController extends Controller {
    public function index(Request $request) {
        $month = $request->get('month', now()->format('Y-m'));
        $expenses = Expense::where('expense_date', 'like', "$month%")->orderByDesc('expense_date')->get();
        return view('expenses.index', ['expenses' => $expenses, 'month' => $month, 'total' => (float)$expenses->sum('amount'), 'byCat' => $expenses->groupBy('category')->map(fn($g) => (float)$g->sum('amount'))]);
    }
    public function store(Request $request) {
        $data = $request->validate(['category' => 'required|string', 'amount' => 'required|numeric|min:0.01', 'expense_date' => 'required|date', 'description' => 'nullable|string']);
        $data['user_id'] = auth()->id(); Expense::create($data); return back()->with('ok', 'Expense recorded.');
    }
}
