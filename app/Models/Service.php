<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['name','description','price','tax_rate','is_recurring','recurring_interval','is_active'];
    protected $casts = ['price'=>'decimal:2','tax_rate'=>'decimal:2','is_recurring'=>'boolean','is_active'=>'boolean'];
}
