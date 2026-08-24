<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            // Basic information
            $table->string('preferred_name')->nullable()->after('name');
            $table->string('profile_photo')->nullable()->after('preferred_name');
            $table->date('date_of_birth')->nullable()->after('profile_photo');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('nic_passport')->nullable()->after('gender');
            $table->text('address')->nullable()->after('phone');
            $table->string('personal_email')->nullable()->after('email');

            // Employment information
            $table->string('employment_type')->nullable()->after('status');
            $table->string('department')->nullable()->after('employment_type');
            $table->string('designation')->nullable()->after('department');
            $table->foreignId('reporting_manager_id')
                ->nullable()
                ->after('designation')
                ->constrained('employees')
                ->nullOnDelete();
            $table->string('work_location')->nullable()->after('reporting_manager_id');
            $table->string('work_mode')->nullable()->after('work_location');

            $table->date('probation_start_date')->nullable()->after('join_date');
            $table->date('probation_end_date')->nullable()->after('probation_start_date');
            $table->date('confirmation_date')->nullable()->after('probation_end_date');
            $table->date('contract_start_date')->nullable()->after('confirmation_date');
            $table->date('contract_end_date')->nullable()->after('contract_start_date');
            $table->date('resignation_date')->nullable()->after('contract_end_date');
            $table->date('termination_date')->nullable()->after('resignation_date');
            $table->text('reason_for_leaving')->nullable()->after('termination_date');

            // Job role
            $table->string('job_title')->nullable()->after('position');
            $table->longText('job_description')->nullable()->after('job_title');
            $table->string('access_level')->nullable()->after('job_description');
            $table->text('working_days')->nullable()->after('access_level');
            $table->string('working_hours')->nullable()->after('working_days');
            $table->boolean('on_call_required')->default(false)->after('working_hours');
            $table->longText('performance_goals')->nullable()->after('on_call_required');
            $table->longText('additional_notes')->nullable()->after('performance_goals');

            // Salary / statutory
            $table->decimal('fixed_allowance', 12, 2)->default(0)->after('allowance');
            $table->decimal('other_allowances', 12, 2)->default(0)->after('fixed_allowance');
            $table->decimal('standard_deduction', 12, 2)->default(0)->after('deduction');
            $table->string('salary_payment_method')->nullable()->after('standard_deduction');
            $table->string('bank_name')->nullable()->after('salary_payment_method');
            $table->string('bank_branch')->nullable()->after('bank_name');
            $table->string('bank_account_holder')->nullable()->after('bank_branch');
            $table->string('bank_account_number')->nullable()->after('bank_account_holder');
            $table->string('epf_membership_no')->nullable()->after('bank_account_number');
            $table->string('etf_details')->nullable()->after('epf_membership_no');
            $table->string('tax_identification_no')->nullable()->after('etf_details');

            // System account relationship
            $table->foreignId('user_id')
                ->nullable()
                ->unique()
                ->after('tax_identification_no')
                ->constrained('users')
                ->nullOnDelete();

            // Account/profile settings
            $table->boolean('system_account_enabled')->default(false)->after('user_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropForeign(['user_id']);

            $table->dropColumn([
                'preferred_name',
                'profile_photo',
                'date_of_birth',
                'gender',
                'nic_passport',
                'address',
                'personal_email',

                'employment_type',
                'department',
                'designation',
                'reporting_manager_id',
                'work_location',
                'work_mode',
                'probation_start_date',
                'probation_end_date',
                'confirmation_date',
                'contract_start_date',
                'contract_end_date',
                'resignation_date',
                'termination_date',
                'reason_for_leaving',

                'job_title',
                'job_description',
                'access_level',
                'working_days',
                'working_hours',
                'on_call_required',
                'performance_goals',
                'additional_notes',

                'fixed_allowance',
                'other_allowances',
                'standard_deduction',
                'salary_payment_method',
                'bank_name',
                'bank_branch',
                'bank_account_holder',
                'bank_account_number',
                'epf_membership_no',
                'etf_details',
                'tax_identification_no',

                'user_id',
                'system_account_enabled',
            ]);
        });
    }
};