<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sms_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('phone'); $table->string('type')->default('general'); $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable(); $table->text('message'); $table->string('status');
            $table->text('provider_response')->nullable(); $table->timestamp('sent_at')->nullable(); $table->timestamps();
            $table->index(['reference_type','reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_logs');
    }
};
