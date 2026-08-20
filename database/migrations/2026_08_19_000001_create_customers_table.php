<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('company_name')->nullable();
            $table->string('email')->nullable(); $table->string('phone'); $table->string('whatsapp')->nullable();
            $table->text('address')->nullable(); $table->text('notes')->nullable(); $table->string('status')->default('active');
            $table->timestamps(); $table->index(['phone','email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
