<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('sms_count');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('validity_days')->nullable();
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['enabled', 'sort_order']);
        });

        DB::table('sms_packages')->insert([
            [
                'name' => '1000 SMS',
                'sms_count' => 1000,
                'price' => 1800.00,
                'validity_days' => null,
                'description' => 'SMS notification package with 1000 SMS.',
                'enabled' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_packages');
    }
};
