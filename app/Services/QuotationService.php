<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Quotation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class QuotationService
{
    public function __construct(private DocumentNumberService $numbers)
    {
    }

    public function convertToInvoice(Quotation $quotation): Invoice
    {
        if ($quotation->status !== 'accepted') {
            throw new RuntimeException('Only accepted quotations can be converted.');
        }

        if ($quotation->invoice) {
            return $quotation->invoice;
        }

        return DB::transaction(function () use ($quotation) {
            $quotation->load('items');

            $invoice = Invoice::create([
                'customer_id' => $quotation->customer_id,
                'quotation_id' => $quotation->id,
                'number' => $this->numbers->invoice(),
                'status' => 'issued',
                'issued_at' => now()->toDateString(),
                'due_at' => now()->addDays(7)->toDateString(),
                'description' => $quotation->description,
                'subtotal' => $quotation->subtotal,
                'discount' => $quotation->discount,
                'tax' => $quotation->tax,
                'total' => $quotation->total,
                'balance' => $quotation->total,
                'notes' => $quotation->notes,
            ]);

            foreach ($quotation->items as $item) {
                $invoice->items()->create([
                    'item_type' => $item->item_type,
                    'item_id' => $item->item_id,
                    'description' => $item->description,
                    'quantity' => $item->quantity,
                    'unit_price' => $item->unit_price,
                    'tax_rate' => $item->tax_rate,
                    'total' => $item->total,
                ]);
            }

            $quotation->update(['status' => 'converted']);

            return $invoice;
        });
    }
}