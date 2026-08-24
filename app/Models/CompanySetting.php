<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompanySetting extends Model
{
    protected $fillable = [
        'company_name',
        'letterhead_path',
        'bank_account_name',
        'bank_name',
        'bank_branch',
        'bank_account_number',
    ];
}