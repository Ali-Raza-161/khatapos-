<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use Illuminate\Support\Facades\Hash;
use App\Models\User; use App\Models\Category; use App\Models\Product; use App\Models\Customer; use App\Models\Supplier; use App\Models\Setting;
class DatabaseSeeder extends Seeder {
    public function run(): void {
        User::firstOrCreate(['email' => 'demo@khatapos.local'], ['name' => 'Demo Owner', 'password' => Hash::make('demo123'), 'role' => 'owner']);
        User::firstOrCreate(['email' => 'cashier@khatapos.local'], ['name' => 'Demo Cashier', 'password' => Hash::make('demo123'), 'role' => 'cashier']);
        Setting::updateOrCreate(['key' => 'shop_name'], ['value' => 'KhataPOS Demo Shop']);
        Setting::updateOrCreate(['key' => 'receipt_footer'], ['value' => 'Thank you for shopping with us!']);
        $cats = [];
        foreach (['Grocery', 'Dairy', 'Household', 'Beverages'] as $n) $cats[$n] = Category::firstOrCreate(['name' => $n]);
        $products = [
            ['Grocery', 'Flour 10kg', '89011001', 'packet', 1050, 1180, 40, 5],
            ['Grocery', 'Sugar 1kg', '89011002', 'kg', 145, 165, 60, 10],
            ['Grocery', 'Rice 1kg', '89011003', 'kg', 280, 320, 35, 5],
            ['Grocery', 'Moong Dal 1kg', '89011004', 'kg', 240, 275, 25, 5],
            ['Grocery', 'Cooking Oil 1L', '89011005', 'litre', 420, 480, 30, 5],
            ['Grocery', 'Tea Leaves 500g', '89011006', 'packet', 650, 720, 20, 3],
            ['Grocery', 'Salt 1kg', '89011007', 'packet', 45, 60, 100, 10],
            ['Dairy', 'Milk 1L', '89011008', 'litre', 190, 220, 15, 5],
            ['Household', 'Washing Soap', '89011009', 'piece', 85, 110, 50, 10],
            ['Household', 'Biscuits Pack', '89011010', 'packet', 60, 80, 80, 10],
            ['Beverages', 'Cold Drink 1.5L', '89011011', 'piece', 150, 180, 24, 6],
            ['Beverages', 'Juice 1L', '89011012', 'piece', 200, 240, 18, 4],
        ];
        foreach ($products as [$cat, $name, $barcode, $unit, $pp, $sp, $stock, $alert])
            Product::firstOrCreate(['barcode' => $barcode], ['category_id' => $cats[$cat]->id, 'name' => $name, 'unit' => $unit, 'purchase_price' => $pp, 'sale_price' => $sp, 'stock_qty' => $stock, 'alert_qty' => $alert]);
        Customer::firstOrCreate(['name' => 'Ahmed Khan'], ['phone' => '03001234567', 'address' => 'Main Market', 'credit_limit' => 20000, 'opening_balance' => 0]);
        Customer::firstOrCreate(['name' => 'Fatima Bibi'], ['phone' => '03219876543', 'address' => 'Street 4', 'credit_limit' => 10000, 'opening_balance' => 0]);
        Supplier::firstOrCreate(['name' => 'Lahore Traders'], ['phone' => '03007654321', 'address' => 'Wholesale Market', 'opening_balance' => 0]);
    }
}
