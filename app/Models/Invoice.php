<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    protected $fillable = [
        'customer_id',
        'quotation_id',
        'number',
        'status',
        'issued_at',
        'due_at',
        'description',
        'subtotal',
        'discount',
        'tax',
        'total',
        'balance',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'due_at' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(Quotation::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing('items');

        $subtotal = $this->items->sum(
            fn ($item) => (float) $item->total
        );

        $discount = (float) $this->discount;
        $tax = (float) $this->tax;
        $total = max(0, $subtotal - $discount + $tax);

        $paid = (float) $this->payments()->sum('amount');

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total' => $total,
            'balance' => max(0, $total - $paid),
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->number)) {
                $invoice->number = app(\App\Services\DocumentNumberService::class)->invoice();
            }
        });
    }
}