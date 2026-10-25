<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Product extends Model {
    use HasFactory;
    protected $table = 'products';
    protected $guarded = [];
    protected $fillable = ['category_id','name','barcode','unit','purchase_price','sale_price','stock_qty','alert_qty','expiry_date','is_active'];
    public function category(){ return $this->belongsTo(Category::class); }
    public function getIsLowAttribute(){ return $this->stock_qty <= $this->alert_qty; }
}
