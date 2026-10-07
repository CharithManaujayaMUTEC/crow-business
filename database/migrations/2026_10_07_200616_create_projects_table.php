<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();

            $table->foreignId('customer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('name');
            $table->text('description')->nullable();

            $table->date('start_date')->nullable();
            $table->date('expected_end_date')->nullable();
            $table->date('completed_date')->nullable();

            $table->decimal('project_value', 15, 2)
                ->default(0);

            $table->unsignedTinyInteger('progress')
                ->default(0);

            $table->string('status')
                ->default('planned');

            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};