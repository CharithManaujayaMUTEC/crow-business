<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    protected $fillable = ['customer_id','phone','type','reference_type','reference_id','message','status','provider_response','sent_at'];
    protected $casts = ['sent_at'=>'datetime'];
    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
}
