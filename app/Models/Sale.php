<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Sale extends Model {
    use HasFactory;
    protected $table = 'sales';
    protected $guarded = [];
    protected $fillable = ['invoice_no','customer_id','user_id','subtotal','discount','total','paid_amount','due_amount','status','cancel_reason'];
    public function items(){ return $this->hasMany(SaleItem::class); }
    public function customer(){ return $this->belongsTo(Customer::class); }
    public function user(){ return $this->belongsTo(User::class); }
}
