<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsPackage extends Model
{
    protected $fillable = [
        'name',
        'sms_count',
        'price',
        'validity_days',
        'description',
        'enabled',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sms_count' => 'integer',
        'validity_days' => 'integer',
        'enabled' => 'boolean',
        'sort_order' => 'integer',
    ];
}
