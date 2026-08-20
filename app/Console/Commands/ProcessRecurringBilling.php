<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\RecurringBilling;
use App\Models\RecurringService;
use App\Services\DocumentNumberService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ProcessRecurringBilling extends Command
{
    protected $signature = 'crow:recurring-billing';
    protected $description = 'Generate invoices for recurring services due today.';

    public function handle(DocumentNumberService $numbers): int
    {
        RecurringService::with(['customer','service'])
            ->where('status', 'active')
            ->whereDate('next_billing_date', '<=', today())
            ->chunkById(100, function ($services) use ($numbers) {
                foreach ($services as $service) {
                    DB::transaction(function () use ($service, $numbers) {
                        $billing = RecurringBilling::firstOrCreate(
                            ['recurring_service_id' => $service->id, 'billing_date' => $service->next_billing_date],
                            ['amount' => $service->amount, 'status' => 'pending']
                        );

                        if ($billing->invoice_id) return;

                        $invoice = Invoice::create([
                            'customer_id' => $service->customer_id,
                            'number' => $numbers->invoice(),
                            'status' => 'issued',
                            'issued_at' => today(),
                            'due_at' => today()->addDays(7),
                            'subtotal' => $service->amount,
                            'tax' => 0,
                            'discount' => 0,
                            'total' => $service->amount,
                            'balance' => $service->amount,
                            'notes' => 'Recurring service: '.$service->service->name,
                        ]);

                        $invoice->items()->create([
                            'item_type' => 'service',
                            'item_id' => $service->service_id,
                            'description' => $service->service->name,
                            'quantity' => 1,
                            'unit_price' => $service->amount,
                            'tax_rate' => 0,
                            'total' => $service->amount,
                        ]);

                        $billing->update(['invoice_id' => $invoice->id, 'status' => 'invoiced']);

                        $service->update([
                            'next_billing_date' => $service->frequency === 'yearly'
                                ? $service->next_billing_date->addYear()
                                : $service->next_billing_date->addMonth(),
                        ]);
                    });
                }
            });

        $this->info('Recurring billing processed.');
        return self::SUCCESS;
    }
}
