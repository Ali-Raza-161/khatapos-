<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class HeldBill extends Model {
    use HasFactory;
    protected $table = 'held_bills';
    protected $guarded = [];
    protected $fillable = ['user_id','name','items_json','discount'];
    
}
