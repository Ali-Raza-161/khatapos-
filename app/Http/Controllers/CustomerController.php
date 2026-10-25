<?php
namespace App\Http\Controllers;
use App\Models\Customer; use App\Models\Payment; use App\Models\ActivityLog;
use App\Services\LedgerService;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class CustomerController extends Controller {
    public function index() {
        $customers = Customer::orderBy('name')->get()->map(fn($c) => ['customer' => $c, 'balance' => LedgerService::customerBalance($c)]);
        return view('customers.index', ['rows' => $customers]);
    }
    public function store(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'phone' => 'nullable|string', 'address' => 'nullable|string', 'credit_limit' => 'nullable|numeric|min:0', 'opening_balance' => 'nullable|numeric']);
        Customer::create($data); return back()->with('ok', 'Customer added.');
    }
    public function show(Customer $customer) {
        $bal = LedgerService::customerBalance($customer);
        return view('customers.ledger', ['customer' => $customer, 'balance' => $bal, 'timeline' => LedgerService::customerTimeline($customer), 'whatsapp' => LedgerService::whatsappLink($customer, $bal)]);
    }
    public function payment(Request $request, Customer $customer) {
        $data = $request->validate(['amount' => 'required|numeric|min:0.01', 'method' => 'required|string', 'note' => 'nullable|string']);
        DB::transaction(function () use ($data, $customer) {
            Payment::create(['type' => 'customer', 'party_id' => $customer->id, 'amount' => $data['amount'], 'method' => $data['method'], 'note' => $data['note'] ?? null, 'user_id' => auth()->id()]);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'customer_payment', 'detail' => "Payment PKR {$data['amount']} from {$customer->name}"]);
        });
        return back()->with('ok', 'Payment recorded. Ledger balance updated.');
    }
    public function destroy(Customer $customer) {
        $customer->delete(); return back()->with('ok', 'Customer deleted.');
    }
}
