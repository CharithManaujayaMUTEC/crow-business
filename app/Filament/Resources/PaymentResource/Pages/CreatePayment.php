<?php

namespace App\Filament\Resources\PaymentResource\Pages;

use App\Filament\Resources\PaymentResource;
use App\Models\Invoice;
use App\Services\PdfDocumentService;
use App\Services\Sms\NotifySmsService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;

    protected function afterCreate(): void
    {
        $payment = $this->record;

        $invoice = Invoice::with('customer')
            ->findOrFail($payment->invoice_id);

        $paid = (float) $invoice->payments()->sum('amount');

        $balance = max(
            0,
            (float) $invoice->total - $paid
        );

        $invoice->update([
            'balance' => $balance,
            'status' => $balance <= 0
                ? 'paid'
                : 'partially_paid',
        ]);

        app(PdfDocumentService::class)
            ->paymentSlip($payment);

        if (! $invoice->customer?->phone) {
            return;
        }

        $paymentAmount = number_format(
            (float) $payment->amount,
            2
        );

        $remainingBalance = number_format(
            (float) $balance,
            2
        );

        $message = "Crow.lk: We received your payment of LKR {$paymentAmount} for invoice {$invoice->number}.";

        if ($balance <= 0) {
            $message .= ' Your invoice has been fully paid. Thank you.';
        } else {
            $message .= " Remaining balance: LKR {$remainingBalance}.";
        }

        app(NotifySmsService::class)->send(
            customer: $invoice->customer,
            message: $message,
            type: 'payment_received',
            referenceType: 'payment',
            referenceId: $payment->id,
        );
    }
}