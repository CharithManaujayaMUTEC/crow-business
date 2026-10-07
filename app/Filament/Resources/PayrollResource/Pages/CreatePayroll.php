<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Employee;
use App\Services\PayrollCalculationService;
use Filament\Resources\Pages\CreateRecord;

class CreatePayroll extends CreateRecord
{
    protected static string $resource = PayrollResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $employee = Employee::find($data['employee_id'] ?? null);

        if (!$employee) {
            return $data;
        }

        $calculation = app(PayrollCalculationService::class)->calculate(
            $employee,
            $data['period'],
            (float) ($data['overtime_amount'] ?? 0),
            (float) ($data['deduction'] ?? 0),
        );

        return array_merge($data, $calculation);
    }
}