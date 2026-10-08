<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\EmployeeTask;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    private function employee(Request $request): Employee
    {
        $employee = Employee::query()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($employee, 403, 'No employee profile is linked to this account.');

        return $employee;
    }

    public function index(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $query = EmployeeTask::query()
            ->with('employee')
            ->where('employee_id', $employee->id)
            ->latest();

        return response()->json([
            'data' => $query->paginate(
                min((int) $request->input('per_page', 20), 100)
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $manager = $this->employee($request);

        $data = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['nullable', 'date'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
        ]);

        $targetEmployee = Employee::query()->findOrFail($data['employee_id']);

        $canManage = $request->user()->is_super_admin
            || $targetEmployee->reporting_manager_id === $manager->id;

        if (! $canManage) {
            return response()->json([
                'message' => 'You can only assign tasks to your direct reports.',
            ], 403);
        }

        $task = EmployeeTask::create([
            'employee_id' => $targetEmployee->id,
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'priority' => $data['priority'],
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Task created successfully.',
            'data' => $task->load('employee'),
        ], 201);
    }

    public function update(
        Request $request,
        EmployeeTask $task
    ): JsonResponse {
        $employee = $this->employee($request);

        $canManage = $request->user()->is_super_admin
            || $task->employee?->reporting_manager_id === $employee->id;

        $isOwner = $task->employee_id === $employee->id;

        if (! $canManage && ! $isOwner) {
            return response()->json([
                'message' => 'You are not authorized to update this task.',
            ], 403);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'due_date' => ['sometimes', 'nullable', 'date'],
            'priority' => ['sometimes', 'in:low,medium,high,urgent'],
            'status' => ['sometimes', 'in:pending,in_progress,completed,cancelled'],
        ]);

        if (array_key_exists('status', $data)) {
            if ($data['status'] === 'completed') {
                $data['completed_at'] = now();
            } else {
                $data['completed_at'] = null;
            }
        }

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully.',
            'data' => $task->fresh()->load('employee'),
        ]);
    }

    public function employees(Request $request): JsonResponse
    {
        $manager = $this->employee($request);

        $query = Employee::query()
            ->where('status', 'active')
            ->orderBy('name');

        if (! $request->user()->is_super_admin) {
            $query->where('reporting_manager_id', $manager->id);
        }

        return response()->json([
            'data' => $query->get([
                'id',
                'employee_no',
                'name',
                'preferred_name',
                'department',
                'position',
                'designation',
            ]),
        ]);
    }
}
