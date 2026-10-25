<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class SaleItem extends Model {
    use HasFactory;
    protected $table = 'sale_items';
    protected $guarded = [];
    protected $fillable = ['sale_id','product_id','quantity','sale_price','purchase_price','line_total'];
    public function sale(){ return $this->belongsTo(Sale::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
