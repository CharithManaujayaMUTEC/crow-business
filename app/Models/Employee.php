<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Employee extends Model
{
    protected $fillable = ['employee_no','name','position','phone','email','join_date','basic_salary','allowance','deduction','status'];
    protected $casts = ['join_date'=>'date','basic_salary'=>'decimal:2','allowance'=>'decimal:2','deduction'=>'decimal:2'];
    public function payrolls(): HasMany { return $this->hasMany(Payroll::class); }
}
