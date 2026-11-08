<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            $table->decimal('development_hourly_rate', 12, 2)->default(0);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('git_repository_url')->nullable();
            $table->foreignId('github_repository_id')->nullable()
                ->constrained('github_repositories')->nullOnDelete();
        });

        Schema::table('github_commits', function (Blueprint $table) {
            $table->decimal('hours', 6, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('github_commits', fn (Blueprint $t) => $t->dropColumn('hours'));

        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('github_repository_id');
            $table->dropColumn('git_repository_url');
        });

        Schema::table('company_settings', fn (Blueprint $t) => $t->dropColumn('development_hourly_rate'));
    }
};
