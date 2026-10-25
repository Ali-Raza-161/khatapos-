<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class StockMovement extends Model {
    use HasFactory;
    protected $table = 'stock_movements';
    protected $guarded = [];
    protected $fillable = ['product_id','type','quantity','reference_type','reference_id','note'];
    public function product(){ return $this->belongsTo(Product::class); }
}
