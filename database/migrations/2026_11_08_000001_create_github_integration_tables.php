<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('github_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('enabled')->default(false);
            $table->string('api_url')->default('https://api.github.com');
            $table->string('organization')->nullable();
            $table->text('token')->nullable(); // stored encrypted
            $table->boolean('auto_sync')->default(true);
            $table->unsignedSmallInteger('initial_days_back')->default(30);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('github_repositories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('github_id')->unique();
            $table->string('name');
            $table->string('full_name')->unique();
            $table->string('default_branch')->nullable();
            $table->string('html_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_private')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_tracked')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_sync_error')->nullable();
            $table->timestamps();
        });

        Schema::create('github_commits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('github_repository_id')->constrained('github_repositories')->cascadeOnDelete();
            $table->string('sha', 40);
            $table->text('message');
            $table->string('author_name')->nullable();
            $table->string('author_email')->nullable();
            $table->string('author_login')->nullable();
            $table->string('author_avatar')->nullable();
            $table->string('committer_name')->nullable();
            $table->string('committer_login')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('committed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['github_repository_id', 'sha']);
            $table->index('author_login');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('github_commits');
        Schema::dropIfExists('github_repositories');
        Schema::dropIfExists('github_settings');
    }
};
