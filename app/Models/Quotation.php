<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Quotation extends Model
{
    protected $fillable = ['customer_id','number','status','issued_at','valid_until','subtotal','discount','tax','total','notes'];
    protected $casts = ['issued_at'=>'date','valid_until'=>'date','subtotal'=>'decimal:2','discount'=>'decimal:2','tax'=>'decimal:2','total'=>'decimal:2'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function items(): HasMany { return $this->hasMany(QuotationItem::class); }
    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne { return $this->hasOne(Invoice::class); }

    protected static function booted(): void
    {
        static::creating(function (Quotation $quotation) {
            if (empty($quotation->number)) {
                $quotation->number = app(\App\Services\DocumentNumberService::class)->quotation();
            }
        });
    }
}
