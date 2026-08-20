<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringService extends Model
{
    protected $fillable = ['customer_id','service_id','amount','frequency','billing_day','start_date','next_billing_date','status','auto_invoice','reminder_days'];
    protected $casts = ['amount'=>'decimal:2','start_date'=>'date','next_billing_date'=>'date','auto_invoice'=>'boolean','reminder_days'=>'array'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function service(): BelongsTo { return $this->belongsTo(Service::class); }
    public function billingRecords(): HasMany { return $this->hasMany(RecurringBilling::class); }
}
