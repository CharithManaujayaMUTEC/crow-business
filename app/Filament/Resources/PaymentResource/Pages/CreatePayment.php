<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\PdfDocumentService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        $payment = $this->record;
        $invoice = Invoice::findOrFail($payment->invoice_id);

        $paid = (float) $invoice->payments()->sum('amount');
        $balance = max(0, (float) $invoice->total - $paid);

        $invoice->update([
            'balance' => $balance,
            'status' => $balance <= 0 ? 'paid' : 'partially_paid',
        ]);

        app(PdfDocumentService::class)->paymentSlip($payment);
    }
}
