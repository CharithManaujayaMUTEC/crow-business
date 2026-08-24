<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->default('Crow.lk (Pvt) Ltd');
            $table->string('letterhead_path')->nullable();

            $table->string('bank_account_name')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_branch')->nullable();
            $table->string('bank_account_number')->nullable();

            $table->timestamps();
        });

        DB::table('company_settings')->insert([
            'company_name' => 'Crow.lk (Pvt) Ltd',
            'letterhead_path' => 'images/company-letterhead.png',
            'bank_account_name' => 'Crow.lk (Pvt) Ltd',
            'bank_name' => 'HNB',
            'bank_branch' => 'Pettah',
            'bank_account_number' => '007010350044',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};