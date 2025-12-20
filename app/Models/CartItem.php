<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CartItem extends Model {
    protected $fillable = ['user_id','product_id','quantity','meta'];
    protected $casts = ['meta'=>'array', 'quantity'=>'integer'];
    public function product(){ return $this->belongsTo(\App\Models\Product::class); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}