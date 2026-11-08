<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GithubCommit extends Model
{
    protected $fillable = [
        'github_repository_id', 'sha', 'message', 'author_name', 'author_email',
        'author_login', 'author_avatar', 'committer_name', 'committer_login',
        'url', 'committed_at', 'hours',
    ];

    protected $casts = ['committed_at' => 'datetime', 'hours' => 'decimal:2'];

    public function repository(): BelongsTo
    {
        return $this->belongsTo(GithubRepository::class, 'github_repository_id');
    }
}
