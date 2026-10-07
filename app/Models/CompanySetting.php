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
        'epf_employee_rate',
        'epf_employer_rate',
        'etf_employer_rate',
    ];

    protected $casts = [
        'epf_employee_rate' => 'decimal:2',
        'epf_employer_rate' => 'decimal:2',
        'etf_employer_rate' => 'decimal:2',
    ];
}