<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            $t->string('role')->default('cashier');
        });
        Schema::create('settings', function (Blueprint $t) {
            $t->id(); $t->string('key')->unique(); $t->text('value')->nullable(); $t->timestamps();
        });
        Schema::create('categories', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->timestamps();
        });
        Schema::create('products', function (Blueprint $t) {
            $t->id(); $t->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name'); $t->string('barcode')->nullable()->unique();
            $t->string('unit')->default('piece');
            $t->decimal('purchase_price', 12, 2)->default(0);
            $t->decimal('sale_price', 12, 2)->default(0);
            $t->decimal('stock_qty', 12, 2)->default(0);
            $t->decimal('alert_qty', 12, 2)->default(0);
            $t->date('expiry_date')->nullable(); $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('phone')->nullable();
            $t->string('address')->nullable(); $t->decimal('credit_limit', 12, 2)->default(0);
            $t->decimal('opening_balance', 12, 2)->default(0); $t->timestamps();
        });
        Schema::create('suppliers', function (Blueprint $t) {
            $t->id(); $t->string('name'); $t->string('phone')->nullable();
            $t->string('address')->nullable(); $t->decimal('opening_balance', 12, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('sales', function (Blueprint $t) {
            $t->id(); $t->string('invoice_no')->unique();
            $t->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('subtotal', 12, 2)->default(0); $t->decimal('discount', 12, 2)->default(0);
            $t->decimal('total', 12, 2)->default(0); $t->decimal('paid_amount', 12, 2)->default(0);
            $t->decimal('due_amount', 12, 2)->default(0);
            $t->string('status')->default('completed'); $t->text('cancel_reason')->nullable();
            $t->timestamps();
        });
        Schema::create('sale_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->decimal('quantity', 12, 2); $t->decimal('sale_price', 12, 2);
            $t->decimal('purchase_price', 12, 2); $t->decimal('line_total', 12, 2);
            $t->timestamps();
        });
        Schema::create('purchases', function (Blueprint $t) {
            $t->id(); $t->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();
            $t->string('invoice_no')->nullable(); $t->decimal('total', 12, 2)->default(0);
            $t->decimal('paid_amount', 12, 2)->default(0); $t->decimal('due_amount', 12, 2)->default(0);
            $t->date('date')->nullable(); $t->timestamps();
        });
        Schema::create('purchase_items', function (Blueprint $t) {
            $t->id(); $t->foreignId('purchase_id')->constrained()->cascadeOnDelete();
            $t->foreignId('product_id')->constrained();
            $t->decimal('quantity', 12, 2); $t->decimal('purchase_price', 12, 2);
            $t->decimal('line_total', 12, 2); $t->timestamps();
        });
        Schema::create('payments', function (Blueprint $t) {
            $t->id(); $t->string('type'); $t->unsignedBigInteger('party_id');
            $t->string('related_type')->nullable(); $t->unsignedBigInteger('related_id')->nullable();
            $t->decimal('amount', 12, 2); $t->string('method')->default('cash');
            $t->text('note')->nullable(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });
        Schema::create('expenses', function (Blueprint $t) {
            $t->id(); $t->string('category'); $t->decimal('amount', 12, 2);
            $t->date('expense_date'); $t->text('description')->nullable();
            $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); $t->timestamps();
        });
        Schema::create('stock_movements', function (Blueprint $t) {
            $t->id(); $t->foreignId('product_id')->constrained();
            $t->string('type'); $t->decimal('quantity', 12, 2);
            $t->string('reference_type')->nullable(); $t->unsignedBigInteger('reference_id')->nullable();
            $t->text('note')->nullable(); $t->timestamps();
        });
        Schema::create('activity_logs', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('action'); $t->text('detail')->nullable(); $t->timestamps();
        });
        Schema::create('held_bills', function (Blueprint $t) {
            $t->id(); $t->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $t->string('name'); $t->text('items_json'); $t->decimal('discount', 12, 2)->default(0);
            $t->timestamps();
        });
    }
    public function down(): void {
        foreach (['held_bills','activity_logs','stock_movements','expenses','payments','purchase_items','purchases','sale_items','sales','suppliers','customers','products','categories','settings'] as $t) Schema::dropIfExists($t);
        Schema::table('users', function (Blueprint $t) { $t->dropColumn('role'); });
    }
};
