<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model {
    use HasFactory;

    protected $fillable = ['user_id','product_id','variant_id','quantity','meta'];
    protected $casts = ['meta'=>'array', 'quantity'=>'integer'];

    public function product(){ return $this->belongsTo(\App\Models\Product::class); }
    public function variant(){ return $this->belongsTo(\App\Models\ProductVariant::class, 'variant_id'); }
    public function user(){ return $this->belongsTo(\App\Models\User::class); }
}