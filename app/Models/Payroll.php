<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    protected $fillable = [
        'employee_id',
        'period',
        'payment_date',
        'basic_salary',
        'allowance',
        'fixed_allowance',
        'other_allowances',
        'overtime_amount',
        'gross_salary',
        'working_days',
        'present_days',
        'absent_days',
        'no_pay_days',
        'deduction',
        'total_deductions',
        'epf_employee',
        'epf_employer',
        'etf_employer',
        'net_salary',
        'status',
        'payslip_path',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'overtime_amount' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'working_days' => 'integer',
        'present_days' => 'integer',
        'absent_days' => 'integer',
        'no_pay_days' => 'decimal:2',
        'deduction' => 'decimal:2',
        'total_deductions' => 'decimal:2',
        'epf_employee' => 'decimal:2',
        'epf_employer' => 'decimal:2',
        'etf_employer' => 'decimal:2',
        'net_salary' => 'decimal:2',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function getTotalAllowancesAttribute(): float
    {
        return (float) (
            $this->allowance +
            $this->fixed_allowance +
            $this->other_allowances +
            $this->overtime_amount
        );
    }

    public function getCalculatedGrossSalaryAttribute(): float
    {
        return (float) (
            $this->basic_salary +
            $this->total_allowances
        );
    }

    public function getCalculatedTotalDeductionsAttribute(): float
    {
        return (float) (
            $this->deduction +
            $this->epf_employee
        );
    }

    public function getCalculatedNetSalaryAttribute(): float
    {
        return (float) (
            $this->gross_salary -
            $this->total_deductions
        );
    }
}