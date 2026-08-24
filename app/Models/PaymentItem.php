<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentItem extends Model
{
    protected $fillable = [
        'payment_id',
        'item_type',
        'item_id',
        'description',
        'quantity',
        'unit_price',
        'tax_rate',
        'total',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    protected static function booted(): void
    {
        static::saving(function (PaymentItem $item) {
            $item->total = round(
                ((float) $item->quantity * (float) $item->unit_price),
                2
            );
        });

        static::saved(function (PaymentItem $item) {
            $item->payment?->updateQuietly([
                'amount' => $item->payment->items()->sum('total'),
            ]);
        });

        static::deleted(function (PaymentItem $item) {
            if ($item->payment) {
                $item->payment->updateQuietly([
                    'amount' => $item->payment->items()->sum('total'),
                ]);
            }
        });
    }
}