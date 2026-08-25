<?php

namespace App\Filament\Resources\InvoiceResource\Pages;

use App\Filament\Resources\InvoiceResource;
use App\Services\DocumentNumberService;
use App\Services\Sms\NotifySmsService;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number'] = app(DocumentNumberService::class)->invoice();

        $data['balance'] = $data['balance']
            ?? ($data['total'] ?? 0);

        return $data;
    }

    protected function afterCreate(): void
    {
        $invoice = $this->record->load('customer');

        if (! $invoice->customer?->phone) {
            return;
        }

        $amount = number_format(
            (float) $invoice->total,
            2
        );

        $message = "Crow.lk: Invoice {$invoice->number} for LKR {$amount} has been issued.";

        if ($invoice->due_at) {
            $message .= ' Payment is due on '
                . $invoice->due_at->format('d/m/Y')
                . '.';
        }

        app(NotifySmsService::class)->send(
            customer: $invoice->customer,
            message: $message,
            type: 'invoice_issued',
            referenceType: 'invoice',
            referenceId: $invoice->id,
        );
    }
}