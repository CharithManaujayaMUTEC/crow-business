<?php

namespace App\Filament\Resources\PayrollResource\Pages;

use App\Filament\Resources\PayrollResource;
use App\Models\Employee;
use App\Services\PayrollCalculationService;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayroll extends EditRecord
{
    protected static string $resource = PayrollResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
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

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}