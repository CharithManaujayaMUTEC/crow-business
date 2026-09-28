<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sms_notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('enabled')->default(true);
            $table->text('message');
            $table->integer('trigger_days')->nullable();
            $table->unsignedInteger('cooldown_minutes')->default(1440);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['enabled', 'trigger_days']);
        });

        $now = now();

        DB::table('sms_notification_templates')->insert([
            [
                'key' => 'payment_reminder_3_days',
                'name' => 'Payment Reminder - 3 Days Before',
                'description' => 'Sent when an outstanding invoice is 3 days away from its due date.',
                'enabled' => true,
                'message' => 'Crow.lk reminder: Invoice {invoice_number} has an outstanding balance of LKR {balance_formatted}. Payment is due on {due_date}.',
                'trigger_days' => 3,
                'cooldown_minutes' => 1440,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'payment_reminder_due_today',
                'name' => 'Payment Reminder - Due Today',
                'description' => 'Sent on the invoice due date when an outstanding balance remains.',
                'enabled' => true,
                'message' => 'Crow.lk reminder: Invoice {invoice_number} is due today. Outstanding balance: LKR {balance_formatted}.',
                'trigger_days' => 0,
                'cooldown_minutes' => 1440,
                'sort_order' => 20,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'payment_reminder_overdue_3',
                'name' => 'Payment Reminder - 3 Days Overdue',
                'description' => 'Sent when an outstanding invoice is 3 days overdue.',
                'enabled' => true,
                'message' => 'Crow.lk reminder: Invoice {invoice_number} is now 3 days overdue. Outstanding balance: LKR {balance_formatted}. Please settle the payment at your earliest convenience.',
                'trigger_days' => -3,
                'cooldown_minutes' => 1440,
                'sort_order' => 30,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'payment_reminder_overdue_7',
                'name' => 'Payment Reminder - 7 Days Overdue',
                'description' => 'Final reminder for an outstanding invoice that is 7 days overdue.',
                'enabled' => true,
                'message' => 'Crow.lk final reminder: Invoice {invoice_number} is now 7 days overdue. Outstanding balance: LKR {balance_formatted}. Please contact us or settle the outstanding amount.',
                'trigger_days' => -7,
                'cooldown_minutes' => 1440,
                'sort_order' => 40,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('sms_notification_templates');
    }
};
