<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class EmployeePortalController extends Controller
{
    private function employeeFor(Request $request): ?Employee
    {
        return Employee::query()
            ->where('user_id', $request->user()->id)
            ->first();
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = \App\Models\User::query()
            ->where('email', $credentials['email'])
            ->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $employee = Employee::query()
            ->where('user_id', $user->id)
            ->first();

        if (! $employee) {
            return response()->json([
                'message' => 'This account is not linked to an employee profile.',
            ], 403);
        }

        if (
            isset($employee->system_account_enabled)
            && ! $employee->system_account_enabled
        ) {
            return response()->json([
                'message' => 'Employee portal access is disabled for this account.',
            ], 403);
        }

        $user->tokens()->delete();

        $token = $user->createToken('desk-crow-lk')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $this->userPayload($user, $employee),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if (! $employee) {
            return response()->json([
                'message' => 'Employee profile not found.',
            ], 403);
        }

        return response()->json([
            'data' => $this->userPayload($request->user(), $employee),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if (! $employee) {
            return response()->json([
                'message' => 'Employee profile not found.',
            ], 403);
        }

        $today = now()->toDateString();

        $todayAttendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $pendingLeaves = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $tasks = \App\Models\EmployeeTask::query()
            ->where('assigned_to_employee_id', $employee->id)
            ->whereIn('status', ['pending', 'in_progress'])
            ->count();

        $managerApprovals = 0;

        if ($this->canManage($request)) {
            $managerApprovals = LeaveRequest::query()
                ->where('status', 'pending')
                ->whereHas('employee', function ($q) use ($employee, $request) {
                    if (! $request->user()->is_super_admin) {
                        $q->where('reporting_manager_id', $employee->id);
                    }
                })
                ->count();
        }

        return response()->json([
            'data' => [
                'employee' => $this->userPayload($request->user(), $employee),
                'today' => [
                    'date' => $today,
                    'attendance' => $todayAttendance,
                ],
                'pending_leaves' => $pendingLeaves,
                'open_tasks' => $tasks,
                'approval_requests' => $managerApprovals,
                'can_manage' => $this->canManage($request),
            ],
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        $query = Attendance::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('attendance_date');

        if ($request->filled('month')) {
            $date = Carbon::createFromFormat('Y-m', $request->string('month'));

            $query
                ->whereYear('attendance_date', $date->year)
                ->whereMonth('attendance_date', $date->month);
        }

        return response()->json([
            'data' => $query->paginate(31),
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);
        $today = now()->toDateString();

        $attendance = Attendance::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $today,
            ],
            [
                'status' => 'present',
                'late_minutes' => 0,
            ]
        );

        if ($attendance->check_in) {
            return response()->json([
                'message' => 'You have already checked in today.',
                'data' => $attendance,
            ], 422);
        }

        $attendance->update([
            'check_in' => now(),
            'status' => 'present',
        ]);

        return response()->json([
            'message' => 'Check-in recorded successfully.',
            'data' => $attendance->fresh(),
        ]);
    }

    public function checkOut(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);
        $today = now()->toDateString();

        $attendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $attendance || ! $attendance->check_in) {
            return response()->json([
                'message' => 'You must check in before checking out.',
            ], 422);
        }

        if ($attendance->check_out) {
            return response()->json([
                'message' => 'You have already checked out today.',
                'data' => $attendance,
            ], 422);
        }

        $checkIn = Carbon::parse($attendance->check_in);
        $checkOut = now();

        $hours = round(
            $checkIn->diffInMinutes($checkOut) / 60,
            2
        );

        $attendance->update([
            'check_out' => $checkOut,
            'working_hours' => $hours,
        ]);

        return response()->json([
            'message' => 'Check-out recorded successfully.',
            'data' => $attendance->fresh(),
        ]);
    }

    public function leaveTypes(): JsonResponse
    {
        return response()->json([
            'data' => LeaveType::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'code',
                    'allocation',
                    'allocation_period',
                    'is_paid',
                ]),
        ]);
    }

    public function leaves(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        return response()->json([
            'data' => LeaveRequest::query()
                ->with('leaveType:id,name,code,allocation,allocation_period,is_paid')
                ->where('employee_id', $employee->id)
                ->latest()
                ->get(),
        ]);
    }

    public function applyLeave(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        $validated = $request->validate([
            'leave_type_id' => [
                'required',
                'integer',
                'exists:leave_types,id',
            ],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $from = Carbon::parse($validated['from_date'])->startOfDay();
        $to = Carbon::parse($validated['to_date'])->startOfDay();

        $days = $from->diffInDays($to) + 1;

        $leave = LeaveRequest::create([
            'employee_id' => $employee->id,
            'leave_type_id' => $validated['leave_type_id'],
            'from_date' => $from->toDateString(),
            'to_date' => $to->toDateString(),
            'days' => $days,
            'reason' => $validated['reason'] ?? null,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => $leave->load('leaveType'),
        ], 201);
    }

    public function cancelLeave(Request $request, LeaveRequest $leaveRequest): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if ((int) $leaveRequest->employee_id !== (int) $employee->id) {
            return response()->json([
                'message' => 'Not authorized.',
            ], 403);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'Only pending leave requests can be cancelled.',
            ], 422);
        }

        $leaveRequest->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'message' => 'Leave request cancelled.',
            'data' => $leaveRequest->fresh()->load('leaveType'),
        ]);
    }

    public function approvalRequests(Request $request): JsonResponse
    {
        $employee = $this->employeeFor($request);

        if (! $this->canManage($request)) {
            return response()->json([
                'message' => 'Not authorized.',
            ], 403);
        }

        $query = LeaveRequest::query()
            ->with([
                'employee:id,name,preferred_name,employee_number,reporting_manager_id',
                'leaveType:id,name,code',
            ])
            ->where('status', 'pending')
            ->latest();

        if (! $request->user()->is_super_admin) {
            $query->whereHas('employee', function ($q) use ($employee) {
                $q->where('reporting_manager_id', $employee->id);
            });
        }

        return response()->json([
            'data' => $query->get(),
        ]);
    }

    public function approveLeave(
        Request $request,
        LeaveRequest $leaveRequest
    ): JsonResponse {
        $employee = $this->employeeFor($request);

        if (! $this->canManage($request)) {
            return response()->json([
                'message' => 'Not authorized.',
            ], 403);
        }

        $targetEmployee = $leaveRequest->employee;

        if (
            ! $request->user()->is_super_admin
            && (int) $targetEmployee->reporting_manager_id !== (int) $employee->id
        ) {
            return response()->json([
                'message' => 'You can only approve leave for your direct reports.',
            ], 403);
        }

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
            'approval_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $leaveRequest->update([
            'status' => $validated['status'],
            'approval_notes' => $validated['approval_notes'] ?? null,
        ]);

        return response()->json([
            'message' => 'Leave request updated successfully.',
            'data' => $leaveRequest->fresh()->load([
                'employee',
                'leaveType',
            ]),
        ]);
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

    private function userPayload($user, Employee $employee): array
    {
        return [
            'id' => $user->id,
            'name' => $employee->preferred_name ?: $employee->name,
            'full_name' => $employee->name,
            'email' => $user->email,
            'employee_id' => $employee->id,
            'employee_number' => $employee->employee_number,
            'designation' => $employee->designation,
            'department' => $employee->department,
            'phone' => $employee->phone,
            'profile_photo' => $employee->profile_photo,
            'join_date' => $employee->join_date,
            'is_super_admin' => (bool) $user->is_super_admin,
        ];
    }
}
