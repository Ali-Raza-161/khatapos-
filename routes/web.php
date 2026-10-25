<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController; use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PosController; use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController; use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseController; use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ReportController; use App\Http\Controllers\SettingsController;
use App\Http\Controllers\BackupController; use App\Http\Controllers\SupportController;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/pos', [PosController::class, 'index'])->name('pos');
    Route::get('/pos/search', [PosController::class, 'search']);
    Route::post('/pos/checkout', [PosController::class, 'checkout']);
    Route::post('/pos/hold', [PosController::class, 'hold']);
    Route::get('/pos/held/{bill}/resume', [PosController::class, 'resume']);
    Route::get('/sales/{sale}/receipt', [PosController::class, 'receipt'])->name('sales.receipt');
    Route::get('/sales/{sale}/return', [PosController::class, 'returnForm'])->name('sales.return');
    Route::post('/sales/{sale}/return', [PosController::class, 'doReturn']);
    Route::post('/sales/{sale}/cancel', [PosController::class, 'cancel'])->middleware('role:owner,manager');
    Route::get('/products', [ProductController::class, 'index'])->name('products.index');
    Route::get('/products/export', [ProductController::class, 'export'])->name('products.export');
    Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/customers/{customer}/ledger', [CustomerController::class, 'show'])->name('customers.ledger');
    Route::post('/customers/{customer}/payment', [CustomerController::class, 'payment']);
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
    Route::get('/suppliers/{supplier}/ledger', [SupplierController::class, 'show'])->name('suppliers.ledger');
    Route::post('/suppliers/{supplier}/payment', [SupplierController::class, 'payment']);
    Route::get('/purchases', [PurchaseController::class, 'index'])->name('purchases.index');
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/day-closing', [DashboardController::class, 'dayClosing'])->name('reports.day-closing');
    Route::get('/reports/export-sales', [ReportController::class, 'exportSales'])->name('reports.export-sales');
    Route::get('/reports/export-credit', [ReportController::class, 'exportCredit'])->name('reports.export-credit');
    Route::get('/support', [SupportController::class, 'index'])->name('support.index');
    Route::post('/support', [SupportController::class, 'store'])->name('support.store');
    Route::get('/backup', [BackupController::class, 'download'])->name('backup.download');
    Route::middleware('role:owner,manager')->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::post('/products/{product}', [ProductController::class, 'update']);
        Route::post('/products/{product}/adjust', [ProductController::class, 'adjust']);
        Route::post('/products/import', [ProductController::class, 'import']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::post('/suppliers', [SupplierController::class, 'store']);
        Route::post('/purchases', [PurchaseController::class, 'store']);
        Route::post('/expenses', [ExpenseController::class, 'store']);
        Route::post('/support/{ticket}', [SupportController::class, 'update']);
    });
    Route::middleware('role:owner')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update']);
        Route::post('/settings/users', [SettingsController::class, 'createUser']);
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy']);
    });
});
