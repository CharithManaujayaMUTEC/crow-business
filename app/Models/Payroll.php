<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = ['employee_id','period','payment_date','basic_salary','allowance','deduction','net_salary','status','notes'];
    protected $casts = ['payment_date'=>'date','basic_salary'=>'decimal:2','allowance'=>'decimal:2','deduction'=>'decimal:2','net_salary'=>'decimal:2'];
    public function employee(): BelongsTo { return $this->belongsTo(Employee::class); }
}
