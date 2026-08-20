<?php

namespace App\Services;

use App\Models\Quotation;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    public function quotation(): string
    {
        return $this->next('QUT', Quotation::class);
    }

    public function invoice(): string
    {
        return $this->next('INV', Invoice::class);
    }

    protected function next(string $prefix, string $model): string
    {
        return DB::transaction(function () use ($prefix, $model) {
            $period = now()->format('Ym');
            $key = strtolower($prefix).'_'.$period;

            $sequence = DB::table('document_sequences')
                ->where('sequence_key', $key)
                ->lockForUpdate()
                ->first();

            $next = ($sequence?->last_number ?? -1) + 1;

            if ($sequence) {
                DB::table('document_sequences')
                    ->where('id', $sequence->id)
                    ->update(['last_number' => $next, 'updated_at' => now()]);
            } else {
                DB::table('document_sequences')->insert([
                    'sequence_key' => $key,
                    'last_number' => $next,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return sprintf('%s-%s-%03d', $prefix, $period, $next);
        });
    }
}
