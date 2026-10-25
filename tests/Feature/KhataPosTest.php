<?php
namespace Tests\Feature;
use Tests\TestCase; use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User; use App\Models\Category; use App\Models\Product; use App\Models\Customer; use App\Models\Supplier;
use App\Services\BillingService; use App\Services\LedgerService; use App\Services\ProfitService;
class KhataPosTest extends TestCase {
    use RefreshDatabase;
    private function setupShop(): array {
        $owner = User::factory()->create(['role' => 'owner']);
        $cat = Category::create(['name' => 'Grocery']);
        $p = Product::create(['category_id' => $cat->id, 'name' => 'Sugar 1kg', 'barcode' => '111', 'unit' => 'kg', 'purchase_price' => 145, 'sale_price' => 165, 'stock_qty' => 100, 'alert_qty' => 5]);
        $c = Customer::create(['name' => 'Test Customer', 'phone' => '03001234567', 'credit_limit' => 50000]);
        return [$owner, $p, $c];
    }
    public function test_cash_bill_reduces_stock_and_has_no_due(): void {
        [$owner, $p, $c] = $this->setupShop();
        $sale = BillingService::checkout([['product_id' => $p->id, 'quantity' => 2]], 0, 330, null, $owner->id);
        $this->assertEquals(98, (float)$p->fresh()->stock_qty);
        $this->assertEquals(0, (float)$sale->due_amount);
        $this->assertEquals(330, (float)$sale->total);
    }
    public function test_partial_and_full_credit_bills_create_ledger_balance(): void {
        [$owner, $p, $c] = $this->setupShop();
        BillingService::checkout([['product_id' => $p->id, 'quantity' => 2]], 0, 130, $c->id, $owner->id); // due 200
        $this->assertEquals(200, LedgerService::customerBalance($c));
        BillingService::checkout([['product_id' => $p->id, 'quantity' => 1]], 0, 0, $c->id, $owner->id); // full credit 165
        $this->assertEquals(365, LedgerService::customerBalance($c));
    }
    public function test_return_restores_stock_and_reduces_balance(): void {
        [$owner, $p, $c] = $this->setupShop();
        $sale = BillingService::checkout([['product_id' => $p->id, 'quantity' => 4]], 0, 0, $c->id, $owner->id);
        $this->assertEquals(96, (float)$p->fresh()->stock_qty);
        BillingService::returnItems($sale, [$sale->items->first()->id => 1], $owner->id);
        $this->assertEquals(97, (float)$p->fresh()->stock_qty);
        $this->assertEquals(495, (float)$sale->fresh()->total);
        $this->assertEquals(495, LedgerService::customerBalance($c));
    }
    public function test_profit_uses_purchase_price_at_sale_time(): void {
        [$owner, $p, $c] = $this->setupShop();
        BillingService::checkout([['product_id' => $p->id, 'quantity' => 2]], 0, 330, null, $owner->id);
        $p->update(['purchase_price' => 999]); // later price change must not rewrite old profit
        $rep = ProfitService::report();
        $this->assertEquals(330, $rep['sales']); $this->assertEquals(290, $rep['cogs']); $this->assertEquals(40, $rep['gross']);
    }
    public function test_cashier_cannot_open_settings_and_manager_cannot_see_profit(): void {
        [$owner, $p, $c] = $this->setupShop();
        $cashier = User::factory()->create(['role' => 'cashier']);
        $this->actingAs($cashier)->get('/settings')->assertForbidden();
        $manager = User::factory()->create(['role' => 'manager']);
        $this->actingAs($manager)->get('/reports')->assertOk()->assertDontSee('Net profit');
    }
    public function test_support_ticket_flow(): void {
        [$owner, $p, $c] = $this->setupShop();
        $this->actingAs($owner)->post('/support', ['name' => 'Owner', 'subject' => 'Printer issue', 'message' => 'Receipt is blank.', 'priority' => 'high'])->assertRedirect();
        $t = \App\Models\SupportTicket::first(); $this->assertEquals('open', $t->status);
        $this->actingAs($owner)->post("/support/{$t->id}", ['status' => 'resolved', 'reply_note' => 'Replaced the paper roll.'])->assertRedirect();
        $this->assertEquals('resolved', $t->fresh()->status);
    }
}
