<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\SmsLog;
use App\Services\Sms\NotifySmsService;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function afterSave(): void
    {
        $payroll = $this->record->fresh(['employee']);

        if (
            $payroll->status !== 'paid' ||
            ! $payroll->employee?->phone
        ) {
            return;
        }

        $alreadySent = SmsLog::query()
            ->where('reference_type', 'payroll')
            ->where('reference_id', $payroll->id)
            ->where('type', 'salary_paid')
            ->exists();

        if ($alreadySent) {
            return;
        }

        $amount = number_format(
            (float) $payroll->net_salary,
            2
        );

        $message =
            "Crow.lk: Your salary payment of LKR {$amount} for {$payroll->period} has been processed successfully.";

        if ($payroll->payment_date) {
            $message .= ' Payment date: '
                . $payroll->payment_date->format('d/m/Y')
                . '.';
        }

        app(NotifySmsService::class)->sendToPhone(
            phone: $payroll->employee->phone,
            message: $message,
            type: 'salary_paid',
            referenceType: 'payroll',
            referenceId: $payroll->id,
        );
    }
}