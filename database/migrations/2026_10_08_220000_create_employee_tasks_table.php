<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_tasks', function (Blueprint $table) {
            $table->id();

            $table->foreignId('assigned_to_employee_id')
                ->constrained('employees')
                ->cascadeOnDelete();

            $table->foreignId('assigned_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->string('title');

            $table->text('description')->nullable();

            $table->date('due_date')->nullable();

            $table->string('priority')->default('medium');

            $table->string('status')->default('pending');

            $table->text('completion_notes')->nullable();

            $table->timestamp('completed_at')->nullable();

            $table->timestamps();

            $table->index(['assigned_to_employee_id', 'status']);
            $table->index(['assigned_by_user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_tasks');
    }
};
