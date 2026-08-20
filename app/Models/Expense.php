<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    protected $fillable = ['expense_date','category','supplier','description','amount','payment_method','reference','invoice_path','quotation_path','notes'];
    protected $casts = ['expense_date'=>'date','amount'=>'decimal:2'];
}
