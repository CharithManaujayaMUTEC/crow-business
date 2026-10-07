<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code')->unique();

            $table->decimal('allocation', 5, 2)->default(0);

            $table->string('allocation_period')->default('yearly');

            $table->boolean('is_paid')->default(true);
            $table->boolean('is_active')->default(true);

            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
