<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Services\Sms\NotifySmsService;
use Illuminate\Console\Command;

class SendPaymentReminders extends Command
{
    protected $signature = 'crow:payment-reminders';
    protected $description = 'Send SMS reminders for invoices due soon or overdue.';

    public function handle(NotifySmsService $sms): int
    {
        Invoice::with('customer')
            ->whereIn('status', ['issued','partially_paid'])
            ->where('balance', '>', 0)
            ->whereDate('due_at', '<=', today()->addDays(3))
            ->whereNotNull('due_at')
            ->chunkById(100, function ($invoices) use ($sms) {
                foreach ($invoices as $invoice) {
                    $message = $invoice->due_at->isPast()
                        ? "Crow.lk payment reminder: invoice {$invoice->number} has an outstanding balance of LKR ".number_format($invoice->balance, 2).". Please settle at your earliest convenience."
                        : "Crow.lk payment reminder: invoice {$invoice->number} for LKR ".number_format($invoice->balance, 2)." is due on {$invoice->due_at->format('d/m/Y')}.";

                    $sms->send($invoice->customer, $message, 'payment_reminder', 'invoice', $invoice->id);
                }
            });

        $this->info('Payment reminders processed.');
        return self::SUCCESS;
    }
}
