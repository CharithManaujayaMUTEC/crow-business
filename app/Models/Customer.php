<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = ['name','company_name','email','phone','whatsapp','address','notes','status'];

    public function quotations(): HasMany { return $this->hasMany(Quotation::class); }
    public function invoices(): HasMany { return $this->hasMany(Invoice::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }
    public function recurringServices(): HasMany { return $this->hasMany(RecurringService::class); }
}
