<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('recurring_billings', function (Blueprint $table) {
            $table->id(); $table->foreignId('recurring_service_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->date('billing_date'); $table->decimal('amount',12,2); $table->string('status')->default('pending');
            $table->timestamp('reminder_sent_at')->nullable(); $table->timestamps();
            $table->unique(['recurring_service_id','billing_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_billings');
    }
};
