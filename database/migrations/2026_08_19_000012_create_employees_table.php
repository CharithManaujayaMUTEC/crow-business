<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id(); $table->string('employee_no')->unique(); $table->string('name');
            $table->string('position')->nullable(); $table->string('phone')->nullable(); $table->string('email')->nullable();
            $table->date('join_date')->nullable(); $table->decimal('basic_salary',12,2)->default(0);
            $table->decimal('allowance',12,2)->default(0); $table->decimal('deduction',12,2)->default(0);
            $table->string('status')->default('active'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
