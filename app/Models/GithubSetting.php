<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GithubSetting extends Model
{
    protected $fillable = [
        'enabled', 'api_url', 'organization', 'token',
        'auto_sync', 'initial_days_back', 'last_synced_at',
    ];

    protected $hidden = ['token'];

    protected $casts = [
        'enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'token' => 'encrypted',
        'last_synced_at' => 'datetime',
    ];

    public static function current(): self
    {
        return static::query()->first() ?? static::create([]);
    }
}
