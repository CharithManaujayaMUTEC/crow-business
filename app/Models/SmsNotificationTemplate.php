<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsNotificationTemplate extends Model
{
    protected $fillable = [
        'key',
        'name',
        'description',
        'enabled',
        'message',
        'trigger_days',
        'cooldown_minutes',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'trigger_days' => 'integer',
        'cooldown_minutes' => 'integer',
        'sort_order' => 'integer',
    ];
}
