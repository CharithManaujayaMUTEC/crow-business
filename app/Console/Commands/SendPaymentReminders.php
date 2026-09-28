<?php

namespace App\Console\Commands;

use App\Models\Invoice;
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

                    $type = match ($daysUntilDue) {
                        3 => 'payment_reminder_3_days',
                        0 => 'payment_reminder_due_today',
                        -3 => 'payment_reminder_overdue_3',
                        -7 => 'payment_reminder_overdue_7',
                        default => null,
                    };

                    if (! $type) {
                        continue;
                    }

                    $sms->sendTemplate(
                        customer: $invoice->customer,
                        type: $type,
                        variables: [
                            'invoice_number' => $invoice->number,
                            'balance' => (string) $invoice->balance,
                            'balance_formatted' => number_format(
                                (float) $invoice->balance,
                                2
                            ),
                            'due_date' => $invoice->due_at->format('d/m/Y'),
                            'days_until_due' => (string) $daysUntilDue,
                        ],
                        referenceType: 'invoice',
                        referenceId: $invoice->id,
                    );
                }
            });

        $this->info('Payment reminders processed.');

        return self::SUCCESS;
    }
}
