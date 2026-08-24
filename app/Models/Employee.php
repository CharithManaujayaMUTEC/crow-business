<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Employee extends Model
{
    protected $fillable = [
        'employee_no',
        'name',
        'preferred_name',
        'profile_photo',
        'date_of_birth',
        'gender',
        'nic_passport',
        'phone',
        'email',
        'personal_email',
        'address',

        'status',
        'employment_type',
        'department',
        'position',
        'designation',
        'reporting_manager_id',
        'work_location',
        'work_mode',
        'join_date',
        'probation_start_date',
        'probation_end_date',
        'confirmation_date',
        'contract_start_date',
        'contract_end_date',
        'resignation_date',
        'termination_date',
        'reason_for_leaving',

        'job_title',
        'job_description',
        'access_level',
        'working_days',
        'working_hours',
        'on_call_required',
        'performance_goals',
        'additional_notes',

        'basic_salary',
        'allowance',
        'fixed_allowance',
        'other_allowances',
        'deduction',
        'standard_deduction',
        'salary_payment_method',
        'bank_name',
        'bank_branch',
        'bank_account_holder',
        'bank_account_number',
        'epf_membership_no',
        'etf_details',
        'tax_identification_no',

        'user_id',
        'system_account_enabled',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'join_date' => 'date',
        'probation_start_date' => 'date',
        'probation_end_date' => 'date',
        'confirmation_date' => 'date',
        'contract_start_date' => 'date',
        'contract_end_date' => 'date',
        'resignation_date' => 'date',
        'termination_date' => 'date',

        'basic_salary' => 'decimal:2',
        'allowance' => 'decimal:2',
        'fixed_allowance' => 'decimal:2',
        'other_allowances' => 'decimal:2',
        'deduction' => 'decimal:2',
        'standard_deduction' => 'decimal:2',

        'on_call_required' => 'boolean',
        'system_account_enabled' => 'boolean',
    ];

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function responsibilities(): HasMany
    {
        return $this->hasMany(EmployeeResponsibility::class)->orderBy('sort_order');
    }

    public function skills(): HasMany
    {
        return $this->hasMany(EmployeeSkill::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(EmployeeProject::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmployeeEmergencyContact::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(EmployeeHistory::class)->latest();
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->preferred_name ?: $this->name;
    }
}