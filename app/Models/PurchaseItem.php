<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class PurchaseItem extends Model {
    use HasFactory;
    protected $table = 'purchase_items';
    protected $guarded = [];
    protected $fillable = ['purchase_id','product_id','quantity','purchase_price','line_total'];
    public function purchase(){ return $this->belongsTo(Purchase::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
