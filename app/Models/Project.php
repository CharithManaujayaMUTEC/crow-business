<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'description',
        'start_date',
        'expected_end_date',
        'completed_date',
        'project_value',
        'progress',
        'status',
        'notes',
        'git_repository_url',
        'github_repository_id',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expected_end_date' => 'date',
            'completed_date' => 'date',
            'project_value' => 'decimal:2',
            'progress' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function githubRepository(): BelongsTo
    {
        return $this->belongsTo(GithubRepository::class);
    }

    /** Commits of the linked repository (matched on the repository id). */
    public function commits(): HasMany
    {
        return $this->hasMany(GithubCommit::class, 'github_repository_id', 'github_repository_id');
    }
}