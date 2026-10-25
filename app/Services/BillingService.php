<?php
namespace App\Services;
use App\Models\Sale; use App\Models\SaleItem; use App\Models\Product; use App\Models\Payment; use App\Models\StockMovement; use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;
class BillingService {
    /** Create a bill inside one transaction: sale + items, stock reduced, ledger via payment row.
     *  $items: [['product_id'=>int,'quantity'=>float,'price'=>float|null]]; $paid may be 0 (full credit). */
    public static function checkout(array $items, float $discount, float $paid, ?int $customerId, int $userId, string $method = 'cash'): Sale {
        return DB::transaction(function () use ($items, $discount, $paid, $customerId, $userId, $method) {
            $subtotal = 0; $lines = [];
            foreach ($items as $it) {
                $p = Product::lockForUpdate()->findOrFail($it['product_id']);
                if ($p->stock_qty < $it['quantity']) throw new \Exception("Insufficient stock: {$p->name} (available {$p->stock_qty})");
                $price = $it['price'] ?? (float)$p->sale_price;
                $line = round($it['quantity'] * $price, 2); $subtotal += $line;
                $lines[] = [$p, $it['quantity'], $price, $line];
            }
            $total = max(0, round($subtotal - $discount, 2));
            if ($paid > $total) throw new \Exception('Paid amount cannot exceed the bill total.');
            if ($paid < $total && !$customerId) throw new \Exception('A customer is required for a partial or full credit bill.');
            $sale = Sale::create([
                'invoice_no' => 'INV-' . date('Ymd') . '-' . str_pad((string)(Sale::count() + 1), 4, '0', STR_PAD_LEFT),
                'customer_id' => $customerId, 'user_id' => $userId, 'subtotal' => round($subtotal, 2),
                'discount' => $discount, 'total' => $total, 'paid_amount' => $paid,
                'due_amount' => round($total - $paid, 2), 'status' => 'completed',
            ]);
            foreach ($lines as [$p, $qty, $price, $line]) {
                SaleItem::create(['sale_id' => $sale->id, 'product_id' => $p->id, 'quantity' => $qty, 'sale_price' => $price, 'purchase_price' => $p->purchase_price, 'line_total' => $line]);
                $p->decrement('stock_qty', $qty);
                StockMovement::create(['product_id' => $p->id, 'type' => 'sale', 'quantity' => -$qty, 'reference_type' => 'sale', 'reference_id' => $sale->id]);
            }
            if ($paid > 0 && $customerId) Payment::create(['type' => 'customer', 'party_id' => $customerId, 'related_type' => 'sale', 'related_id' => $sale->id, 'amount' => $paid, 'method' => $method, 'user_id' => $userId]);
            ActivityLog::create(['user_id' => $userId, 'action' => 'sale_created', 'detail' => "Bill {$sale->invoice_no} total PKR {$total}, paid PKR {$paid}"]);
            return $sale;
        });
    }
    /** Return items: stock comes back, sale total/due shrink. Never deletes the bill. */
    public static function returnItems(Sale $sale, array $returns, int $userId): Sale {
        return DB::transaction(function () use ($sale, $returns, $userId) {
            if ($sale->status === 'cancelled') throw new \Exception('Returns are not allowed on a cancelled bill.');
            foreach ($returns as $itemId => $qty) {
                $item = SaleItem::where('sale_id', $sale->id)->findOrFail($itemId);
                if ($qty <= 0 || $qty > $item->quantity) throw new \Exception('Invalid return quantity.');
                $item->quantity -= $qty; $item->line_total = round($item->quantity * $item->sale_price, 2); $item->save();
                $p = Product::lockForUpdate()->find($item->product_id); $p->increment('stock_qty', $qty);
                StockMovement::create(['product_id' => $p->id, 'type' => 'return', 'quantity' => $qty, 'reference_type' => 'sale', 'reference_id' => $sale->id, 'note' => 'Return']);
            }
            $sale->refresh();
            $subtotal = (float)$sale->items()->sum('line_total');
            $sale->subtotal = $subtotal; $sale->total = max(0, round($subtotal - (float)$sale->discount, 2));
            if ((float)$sale->paid_amount > $sale->total) $sale->paid_amount = $sale->total; // overpaid part is refunded outside the ledger
            $sale->due_amount = round($sale->total - (float)$sale->paid_amount, 2);
            $sale->status = $sale->items()->sum('quantity') <= 0 ? 'returned' : 'completed';
            $sale->save();
            ActivityLog::create(['user_id' => $userId, 'action' => 'sale_returned', 'detail' => "Return on {$sale->invoice_no}"]);
            return $sale;
        });
    }
    /** Cancel with a reason — bills are never deleted (doc §14). Stock is restored. */
    public static function cancel(Sale $sale, string $reason, int $userId): Sale {
        return DB::transaction(function () use ($sale, $reason, $userId) {
            if ($sale->status === 'cancelled') throw new \Exception('This bill is already cancelled.');
            foreach ($sale->items as $item) {
                $p = Product::lockForUpdate()->find($item->product_id); $p->increment('stock_qty', $item->quantity);
                StockMovement::create(['product_id' => $p->id, 'type' => 'return', 'quantity' => $item->quantity, 'reference_type' => 'sale', 'reference_id' => $sale->id, 'note' => 'Cancelled: ' . $reason]);
            }
            $sale->status = 'cancelled'; $sale->cancel_reason = $reason; $sale->save();
            ActivityLog::create(['user_id' => $userId, 'action' => 'sale_cancelled', 'detail' => "Bill {$sale->invoice_no} cancelled: {$reason}"]);
            return $sale;
        });
    }
}
