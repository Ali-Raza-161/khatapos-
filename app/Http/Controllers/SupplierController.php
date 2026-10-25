<?php
namespace App\Http\Controllers;
use App\Models\Supplier; use App\Models\Payment; use App\Models\ActivityLog;
use App\Services\LedgerService;
use Illuminate\Http\Request; use Illuminate\Support\Facades\DB;
class SupplierController extends Controller {
    public function index() {
        $rows = Supplier::orderBy('name')->get()->map(fn($s) => ['supplier' => $s, 'balance' => LedgerService::supplierBalance($s)]);
        return view('suppliers.index', ['rows' => $rows]);
    }
    public function store(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'phone' => 'nullable|string', 'address' => 'nullable|string', 'opening_balance' => 'nullable|numeric']);
        Supplier::create($data); return back()->with('ok', 'Supplier added.');
    }
    public function show(Supplier $supplier) {
        return view('suppliers.ledger', ['supplier' => $supplier, 'balance' => LedgerService::supplierBalance($supplier), 'purchases' => $supplier->purchases()->orderByDesc('created_at')->get(), 'payments' => Payment::where('type', 'supplier')->where('party_id', $supplier->id)->orderByDesc('created_at')->get()]);
    }
    public function payment(Request $request, Supplier $supplier) {
        $data = $request->validate(['amount' => 'required|numeric|min:0.01', 'method' => 'required|string', 'note' => 'nullable|string']);
        DB::transaction(function () use ($data, $supplier) {
            Payment::create(['type' => 'supplier', 'party_id' => $supplier->id, 'amount' => $data['amount'], 'method' => $data['method'], 'note' => $data['note'] ?? null, 'user_id' => auth()->id()]);
            ActivityLog::create(['user_id' => auth()->id(), 'action' => 'supplier_payment', 'detail' => "Payment PKR {$data['amount']} to {$supplier->name}"]);
        });
        return back()->with('ok', 'Payment to supplier recorded.');
    }
}
