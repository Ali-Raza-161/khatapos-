<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Supplier extends Model {
    use HasFactory;
    protected $table = 'suppliers';
    protected $guarded = [];
    protected $fillable = ['name','phone','address','opening_balance'];
    public function purchases(){ return $this->hasMany(Purchase::class); }
}
