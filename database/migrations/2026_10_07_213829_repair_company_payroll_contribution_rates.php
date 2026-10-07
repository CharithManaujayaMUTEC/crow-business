<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('company_settings', 'epf_employee_rate')) {
                $table->decimal('epf_employee_rate', 5, 2)->default(8.00);
            }

            if (! Schema::hasColumn('company_settings', 'epf_employer_rate')) {
                $table->decimal('epf_employer_rate', 5, 2)->default(12.00);
            }

            if (! Schema::hasColumn('company_settings', 'etf_employer_rate')) {
                $table->decimal('etf_employer_rate', 5, 2)->default(3.00);
            }
        });
    }

    public function down(): void
    {
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'epf_employee_rate')) {
                $table->dropColumn('epf_employee_rate');
            }

            if (Schema::hasColumn('company_settings', 'epf_employer_rate')) {
                $table->dropColumn('epf_employer_rate');
            }

            if (Schema::hasColumn('company_settings', 'etf_employer_rate')) {
                $table->dropColumn('etf_employer_rate');
            }
        });
    }
};