<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    // Разрешаем массовое присвоение этих полей
    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
    ];
}
