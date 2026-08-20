<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->id(); $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('number')->unique(); $table->string('status')->default('draft');
            $table->date('issued_at')->nullable(); $table->date('valid_until')->nullable();
            $table->decimal('subtotal',12,2)->default(0); $table->decimal('discount',12,2)->default(0);
            $table->decimal('tax',12,2)->default(0); $table->decimal('total',12,2)->default(0);
            $table->text('notes')->nullable(); $table->timestamps(); $table->index(['customer_id','status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
