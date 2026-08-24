<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->text('description')->nullable()->after('valid_until');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->text('description')->nullable()->after('due_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn('description');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};