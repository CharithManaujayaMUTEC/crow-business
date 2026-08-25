<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Quotation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PdfController extends Controller
{
    public function quotation(Quotation $quotation): Response
    {
        $quotation->load([
            'customer',
            'items',
        ]);

        $pdf = Pdf::loadView('pdf.quotation', [
            'quotation' => $quotation,
        ])->setPaper('a4');

        return $pdf->stream(
            $quotation->number . '.pdf'
        );
    }

    public function invoice(Invoice $invoice): Response
    {
        $invoice->load([
            'customer',
            'quotation',
            'items',
            'payments',
        ]);

        $pdf = Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice,
        ])->setPaper('a4');

        return $pdf->stream(
            $invoice->number . '.pdf'
        );
    }

    public function payment(Payment $payment): Response
    {
        $payment->load([
            'customer',
            'invoice',
            'items',
        ]);

        $pdf = Pdf::loadView('pdf.payment-slip', [
            'payment' => $payment,
        ])->setPaper('a4');

        return $pdf->stream(
            'PAY-' . $payment->id . '.pdf'
        );
    }
}