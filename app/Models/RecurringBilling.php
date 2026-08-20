<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecurringBilling extends Model
{
    protected $fillable = ['recurring_service_id','invoice_id','billing_date','amount','status','reminder_sent_at'];
    protected $casts = ['billing_date'=>'date','amount'=>'decimal:2','reminder_sent_at'=>'datetime'];
    public function recurringService(): BelongsTo { return $this->belongsTo(RecurringService::class); }
    public function invoice(): BelongsTo { return $this->belongsTo(Invoice::class); }
}
