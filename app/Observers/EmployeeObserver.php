<?php

namespace App\Observers;

use App\Models\Employee;
use App\Models\EmployeeHistory;
use Illuminate\Support\Facades\Auth;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        EmployeeHistory::create([
            'employee_id' => $employee->id,
            'type' => 'created',
            'changed_by' => Auth::id(),
            'notes' => 'Employee profile created.',
        ]);
    }

    public function updated(Employee $employee): void
    {
        $trackedFields = [
            'basic_salary' => 'salary_revision',
            'designation' => 'designation_change',
            'department' => 'department_change',
            'status' => 'status_change',
            'position' => 'designation_change',
            'employment_type' => 'employment_type_change',
        ];

        foreach ($trackedFields as $field => $type) {
            if (! $employee->wasChanged($field)) {
                continue;
            }

            EmployeeHistory::create([
                'employee_id' => $employee->id,
                'type' => $type,
                'field' => $field,
                'old_value' => $employee->getOriginal($field),
                'new_value' => $employee->getAttribute($field),
                'changed_by' => Auth::id(),
            ]);
        }
    }
}