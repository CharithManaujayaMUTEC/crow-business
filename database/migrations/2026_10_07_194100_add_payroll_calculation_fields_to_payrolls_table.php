<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->decimal('fixed_allowance', 12, 2)->default(0)->after('allowance');
            $table->decimal('other_allowances', 12, 2)->default(0)->after('fixed_allowance');

            $table->decimal('overtime_amount', 12, 2)->default(0)->after('other_allowances');

            $table->decimal('gross_salary', 12, 2)->default(0)->after('overtime_amount');

            $table->integer('working_days')->default(0)->after('gross_salary');
            $table->integer('present_days')->default(0)->after('working_days');
            $table->integer('absent_days')->default(0)->after('present_days');
            $table->decimal('no_pay_days', 5, 2)->default(0)->after('absent_days');

            $table->decimal('epf_employee', 12, 2)->default(0)->after('no_pay_days');
            $table->decimal('epf_employer', 12, 2)->default(0)->after('epf_employee');
            $table->decimal('etf_employer', 12, 2)->default(0)->after('epf_employer');

            $table->decimal('total_deductions', 12, 2)->default(0)->after('deduction');

            $table->string('payslip_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('payrolls', function (Blueprint $table) {
            $table->dropColumn([
                'fixed_allowance',
                'other_allowances',
                'overtime_amount',
                'gross_salary',
                'working_days',
                'present_days',
                'absent_days',
                'no_pay_days',
                'epf_employee',
                'epf_employer',
                'etf_employer',
                'total_deductions',
                'payslip_path',
            ]);
        });
    }
};
