<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurring_services', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->restrictOnDelete(); $table->decimal('amount',12,2);
            $table->string('frequency')->default('monthly'); $table->unsignedTinyInteger('billing_day')->default(1);
            $table->date('start_date'); $table->date('next_billing_date'); $table->string('status')->default('active');
            $table->boolean('auto_invoice')->default(true); $table->json('reminder_days')->nullable(); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_services');
    }
};
