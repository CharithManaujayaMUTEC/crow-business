<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Quotation extends Model
{
    protected $fillable = [
        'customer_id',
        'number',
        'status',
        'issued_at',
        'valid_until',
        'description',
        'subtotal',
        'discount',
        'tax',
        'total',
        'notes',
    ];

    protected $casts = [
        'issued_at' => 'date',
        'valid_until' => 'date',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(QuotationItem::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function recalculateTotals(): void
    {
        $this->loadMissing('items');

        $subtotal = $this->items->sum(
            fn ($item) => (float) $item->total
        );

        $discount = (float) $this->discount;
        $tax = (float) $this->tax;

        $this->updateQuietly([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $discount + $tax),
        ]);
    }

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->number)) {
                $quotation->number = app(\App\Services\DocumentNumberService::class)->quotation();
            }
        });
    }
}