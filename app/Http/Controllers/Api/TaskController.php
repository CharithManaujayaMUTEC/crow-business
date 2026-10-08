<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    private function employeeFor(Request $request): ?Employee
    {
        return Employee::query()
            ->where('user_id', $request->user()->id)
            ->first();
    }

    private function canManage(Request $request): bool
    {
        if ((bool) $request->user()->is_super_admin) {
            return true;
        }

        $employee = $this->employeeFor($request);

        return $employee !== null
            && Employee::query()
                ->where('reporting_manager_id', $employee->id)
                ->exists();
    }

    public function index(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if (! $employee) {
            return response()->json([
                'message' => 'No employee profile is linked to this account.',
            ], 403);
        }

        $query = EmployeeTask::query()
            ->with([
                'assignedTo:id,name,preferred_name,employee_number',
                'assignedBy:id,name',
            ])
            ->latest();

        if ($this->canManage($request)) {
            $query->where(function ($q) use ($employee) {
                $q->where('assigned_to_employee_id', $employee->id)
                    ->orWhere('assigned_by_user_id', $request->user()->id);
            });
        } else {
            $query->where('assigned_to_employee_id', $employee->id);
        }

        return response()->json([
            'data' => $query->get(),
            'can_manage' => $this->canManage($request),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        if (! $this->canManage($request)) {
            return response()->json([
                'message' => 'You are not authorized to assign tasks.',
            ], 403);
        }

        $employee = $this->employeeFor($request);

        $validated = $request->validate([
            'assigned_to_employee_id' => [
                'required',
                'integer',
                'exists:employees,id',
            ],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => [
                'required',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
        ]);

        $target = Employee::findOrFail($validated['assigned_to_employee_id']);

        if (
            ! $request->user()->is_super_admin
            && (int) $target->reporting_manager_id !== (int) $employee->id
        ) {
            return response()->json([
                'message' => 'You can only assign tasks to your direct reports.',
            ], 403);
        }

        $task = EmployeeTask::create([
            ...$validated,
            'assigned_by_user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Task assigned successfully.',
            'data' => $task->load([
                'assignedTo:id,name,preferred_name,employee_number',
                'assignedBy:id,name',
            ]),
        ], 201);
    }

    public function update(Request $request, EmployeeTask $task): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if (! $employee) {
            return response()->json([
                'message' => 'Employee profile not found.',
            ], 403);
        }

        $isOwner = (int) $task->assigned_to_employee_id === (int) $employee->id;

        if (! $isOwner && ! $this->canManage($request)) {
            return response()->json([
                'message' => 'You are not authorized to update this task.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'sometimes',
                Rule::in(['pending', 'in_progress', 'completed', 'cancelled']),
            ],
            'completion_notes' => ['nullable', 'string'],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'due_date' => ['nullable', 'date'],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
        ]);

        if (
            isset($validated['status'])
            && $validated['status'] === 'completed'
        ) {
            $validated['completed_at'] = now();
        }

        if (
            isset($validated['status'])
            && $validated['status'] !== 'completed'
        ) {
            $validated['completed_at'] = null;
        }

        $task->update($validated);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $task->fresh()->load([
                'assignedTo:id,name,preferred_name,employee_number',
                'assignedBy:id,name',
            ]),
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        if (! $this->canManage($request)) {
            return response()->json([
                'message' => 'Not authorized.',
            ], 403);
        }

        $employee = $this->employeeFor($request);

        $query = Employee::query()
            ->where('employment_status', 'active')
            ->select([
                'id',
                'employee_number',
                'name',
                'preferred_name',
                'reporting_manager_id',
            ])
            ->orderBy('name');

        if (! $request->user()->is_super_admin) {
            $query->where('reporting_manager_id', $employee->id);
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }
}
