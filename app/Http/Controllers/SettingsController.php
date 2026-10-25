<?php
namespace App\Http\Controllers;
use App\Models\Setting; use App\Models\User; use App\Models\ActivityLog;
use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash;
class SettingsController extends Controller {
    public function index() { return view('settings.index', ['users' => User::orderBy('name')->get(), 'logs' => ActivityLog::orderByDesc('created_at')->limit(50)->get()]); }
    public function update(Request $request) {
        $data = $request->validate(['shop_name' => 'nullable|string', 'shop_phone' => 'nullable|string', 'shop_email' => 'nullable|email', 'receipt_footer' => 'nullable|string', 'printer_size' => 'nullable|string']);
        foreach ($data as $k => $v) Setting::updateOrCreate(['key' => $k], ['value' => $v]);
        return back()->with('ok', 'Settings saved.');
    }
    public function createUser(Request $request) {
        $data = $request->validate(['name' => 'required|string', 'email' => 'required|email|unique:users', 'password' => 'required|min:6', 'role' => 'required|in:owner,manager,cashier']);
        $data['password'] = Hash::make($data['password']); User::create($data);
        return back()->with('ok', 'User created.');
    }
}
