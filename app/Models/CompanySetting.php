<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'legal_name',
        'registration_number',
        'address',
        'phone',
        'email',
        'website',
        'logo',
        'letterhead_path',
        'bank_account_name',
        'bank_name',
        'bank_branch',
        'bank_account_number',
        'epf_employee_rate',
        'epf_employer_rate',
        'etf_employer_rate',
        'development_hourly_rate',
    ];

    protected $casts = [
        'development_hourly_rate' => 'decimal:2',
        'epf_employee_rate' => 'decimal:2',
        'epf_employer_rate' => 'decimal:2',
        'etf_employer_rate' => 'decimal:2',
    ];
}