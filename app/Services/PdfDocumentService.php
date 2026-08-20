<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfDocumentService
{
    public function quotation(Quotation $quotation): string
    {
        $quotation->loadMissing(['customer', 'items']);

        $path = 'quotations/' . $quotation->number . '.pdf';

        Storage::disk('public')->put(
            $path,
            Pdf::loadView('pdf.quotation', compact('quotation'))->output()
        );

        $quotation->update([
            'pdf_path' => $path,
        ]);

        return $path;
    }

    public function invoice(Invoice $invoice): string
    {
        $invoice->loadMissing([
            'customer',
            'quotation',
            'items',
            'payments',
        ]);

        $path = 'invoices/' . $invoice->number . '.pdf';

        Storage::disk('public')->put(
            $path,
            Pdf::loadView('pdf.invoice', compact('invoice'))->output()
        );

        $invoice->update([
            'pdf_path' => $path,
        ]);

        return $path;
    }

    public function paymentSlip(Payment $payment): string
    {
        $payment->loadMissing([
            'customer',
            'invoice',
        ]);

        $filename = 'PAY-' . $payment->id . '-' . now()->format('YmdHis') . '.pdf';

        $path = 'payment-slips/' . $filename;

        Storage::disk('public')->put(
            $path,
            Pdf::loadView('pdf.payment-slip', compact('payment'))->output()
        );

        $payment->update([
            'payment_slip_path' => $path,
        ]);

        return $path;
    }
}