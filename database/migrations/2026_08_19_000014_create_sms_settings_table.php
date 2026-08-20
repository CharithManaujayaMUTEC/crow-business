<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id(); $table->boolean('enabled')->default(false); $table->string('api_url')->nullable();
            $table->string('api_user_id')->nullable(); $table->text('api_key')->nullable();
            $table->string('sender_id')->default('Crow.lk'); $table->string('country_code')->default('94'); $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_settings');
    }
};
