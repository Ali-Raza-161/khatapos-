<?php
namespace App\Services;
use App\Models\Customer; use App\Models\Supplier; use App\Models\Sale; use App\Models\Purchase; use App\Models\Payment;
/** Ledger rule (doc §9): customer balance = opening + unpaid sale amounts - payments received.
 *  Payments include cash received at checkout AND later ledger payments, so a sale's
 *  paid_amount is never subtracted twice. Supplier balance = purchases - supplier payments. */
class LedgerService {
    public static function customerBalance(Customer $c): float {
        $sales = Sale::where('customer_id', $c->id)->where('status', '!=', 'cancelled')->sum('total');
        $paid = Payment::where('type', 'customer')->where('party_id', $c->id)->sum('amount');
        return round((float)$c->opening_balance + (float)$sales - (float)$paid, 2);
    }
    public static function customerTimeline(Customer $c): array {
        $rows = []; $bal = (float)$c->opening_balance;
        $rows[] = ['date' => $c->created_at?->format('Y-m-d'), 'type' => 'Opening balance', 'debit' => $bal, 'credit' => 0, 'balance' => $bal];
        $events = [];
        foreach (Sale::where('customer_id', $c->id)->where('status', '!=', 'cancelled')->orderBy('created_at')->get() as $s)
            $events[] = ['ts' => $s->created_at, 'date' => $s->created_at->format('Y-m-d H:i'), 'type' => 'Bill ' . $s->invoice_no, 'debit' => (float)$s->total, 'credit' => 0];
        foreach (Payment::where('type', 'customer')->where('party_id', $c->id)->orderBy('created_at')->get() as $p)
            $events[] = ['ts' => $p->created_at, 'date' => $p->created_at->format('Y-m-d H:i'), 'type' => 'Payment (' . $p->method . ')', 'debit' => 0, 'credit' => (float)$p->amount];
        usort($events, fn($a, $b) => $a['ts'] <=> $b['ts']);
        foreach ($events as $e) { $bal += $e['debit'] - $e['credit']; $rows[] = $e + ['balance' => round($bal, 2)]; }
        return $rows;
    }
    public static function supplierBalance(Supplier $s): float {
        $p = Purchase::where('supplier_id', $s->id)->sum('total');
        $paid = Payment::where('type', 'supplier')->where('party_id', $s->id)->sum('amount');
        return round((float)$s->opening_balance + (float)$p - (float)$paid, 2);
    }
    public static function whatsappLink(Customer $c, float $balance): ?string {
        if (!$c->phone) return null;
        $digits = preg_replace('/\D/', '', $c->phone);
        if (str_starts_with($digits, '0')) $digits = '92' . substr($digits, 1);
        $msg = "Hello {$c->name}, your pending balance at the shop is PKR " . number_format($balance, 0) . ". Please pay at your earliest convenience. Thank you!";
        return 'https://wa.me/' . $digits . '?text=' . urlencode($msg);
    }
}
