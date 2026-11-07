<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GithubRepository extends Model
{
    protected $fillable = [
        'github_id', 'name', 'full_name', 'default_branch', 'html_url', 'description',
        'is_private', 'is_archived', 'is_tracked', 'last_synced_at', 'last_sync_error',
    ];

    protected $casts = [
        'is_private' => 'boolean',
        'is_archived' => 'boolean',
        'is_tracked' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function commits(): HasMany
    {
        return $this->hasMany(GithubCommit::class);
    }
}
