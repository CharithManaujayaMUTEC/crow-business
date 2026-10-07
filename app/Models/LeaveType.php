<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeaveType extends Model
{
    protected $fillable = [
        'name',
        'code',
        'allocation',
        'allocation_period',
        'is_paid',
        'is_active',
        'description',
    ];

    protected $casts = [
        'allocation' => 'decimal:2',
        'is_paid' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function requests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
