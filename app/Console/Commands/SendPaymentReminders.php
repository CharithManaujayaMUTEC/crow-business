<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\SmsLog;
use App\Services\Sms\NotifySmsService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'crow:payment-reminders';

    protected $description = 'Send controlled SMS reminders for outstanding invoices.';

    public function handle(
        NotifySmsService $sms
    ): int {
        Invoice::with('customer')
            ->whereIn('status', [
                'issued',
                'partially_paid',
            ])
            ->where('balance', '>', 0)
            ->whereNotNull('due_at')
            ->chunkById(100, function ($invoices) use ($sms) {
                foreach ($invoices as $invoice) {
                    if (! $invoice->customer?->phone) {
                        continue;
                    }

                    $daysUntilDue = today()->diffInDays(
                        $invoice->due_at,
                        false
                    );

                    $type = null;
                    $message = null;

                    if ($daysUntilDue === 3) {
                        $type = 'payment_reminder_3_days';

                        $message =
                            "Crow.lk reminder: Invoice {$invoice->number} has an outstanding balance of LKR "
                            . number_format((float) $invoice->balance, 2)
                            . '. Payment is due on '
                            . $invoice->due_at->format('d/m/Y')
                            . '.';
                    }

                    if ($daysUntilDue === 0) {
                        $type = 'payment_reminder_due_today';

                        $message =
                            "Crow.lk reminder: Invoice {$invoice->number} is due today. Outstanding balance: LKR "
                            . number_format((float) $invoice->balance, 2)
                            . '.';
                    }

                    if ($daysUntilDue === -3) {
                        $type = 'payment_reminder_overdue_3';

                        $message =
                            "Crow.lk reminder: Invoice {$invoice->number} is now 3 days overdue. Outstanding balance: LKR "
                            . number_format((float) $invoice->balance, 2)
                            . '. Please settle the payment at your earliest convenience.';
                    }

                    if ($daysUntilDue === -7) {
                        $type = 'payment_reminder_overdue_7';

                        $message =
                            "Crow.lk final reminder: Invoice {$invoice->number} is now 7 days overdue. Outstanding balance: LKR "
                            . number_format((float) $invoice->balance, 2)
                            . '. Please contact us or settle the outstanding amount.';
                    }

                    if (! $type || ! $message) {
                        continue;
                    }

                    $alreadySent = SmsLog::query()
                        ->where('reference_type', 'invoice')
                        ->where('reference_id', $invoice->id)
                        ->where('type', $type)
                        ->exists();

                    if ($alreadySent) {
                        continue;
                    }

                    $sms->send(
                        customer: $invoice->customer,
                        message: $message,
                        type: $type,
                        referenceType: 'invoice',
                        referenceId: $invoice->id,
                    );
                }
            });

        $this->info('Payment reminders processed.');

        return self::SUCCESS;
    }
}