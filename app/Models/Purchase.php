<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Purchase extends Model {
    use HasFactory;
    protected $table = 'purchases';
    protected $guarded = [];
    protected $fillable = ['supplier_id','invoice_no','total','paid_amount','due_amount','date'];
    public function items(){ return $this->hasMany(PurchaseItem::class); }
    public function supplier(){ return $this->belongsTo(Supplier::class); }
}
