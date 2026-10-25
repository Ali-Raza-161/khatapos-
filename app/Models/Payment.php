<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model {
    use HasFactory;
    protected $table = 'payments';
    protected $guarded = [];
    protected $fillable = ['type','party_id','related_type','related_id','amount','method','note','user_id'];
    
}
