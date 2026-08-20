<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','sku','description','price','tax_rate','is_active'];
    protected $casts = ['price'=>'decimal:2','tax_rate'=>'decimal:2','is_active'=>'boolean'];
}
