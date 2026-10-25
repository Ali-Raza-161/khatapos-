<?php
namespace App\Services;
use App\Models\Sale; use App\Models\SaleItem; use App\Models\Expense;
use Illuminate\Support\Facades\DB;
/** Profit (doc §9): COGS uses purchase_price saved on each sale item at sale time,
 *  so later price changes never rewrite old profit. Gross = sales - COGS, net = gross - expenses. */
class ProfitService {
    public static function report($from = null, $to = null): array {
        $sales = Sale::where('status', '!=', 'cancelled');
        if ($from) $sales->whereDate('created_at', '>=', $from);
        if ($to) $sales->whereDate('created_at', '<=', $to);
        $saleIds = (clone $sales)->pluck('id');
        $total = (float)(clone $sales)->sum('total');
        $cogs = (float)SaleItem::whereIn('sale_id', $saleIds)->selectRaw('COALESCE(SUM(quantity * purchase_price),0) as c')->value('c');
        $exp = Expense::query();
        if ($from) $exp->whereDate('expense_date', '>=', $from);
        if ($to) $exp->whereDate('expense_date', '<=', $to);
        $expenses = (float)$exp->sum('amount');
        return ['sales' => round($total, 2), 'cogs' => round($cogs, 2), 'gross' => round($total - $cogs, 2), 'expenses' => round($expenses, 2), 'net' => round($total - $cogs - $expenses, 2)];
    }
}
