<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\Payroll;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeePortalController extends Controller
{
    private function employee(Request $request): Employee
    {
        $employee = Employee::query()
            ->where('user_id', $request->user()->id)
            ->first();

        abort_unless($employee, 403, 'No employee profile is linked to this account.');

        return $employee;
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
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 422);
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
            isset($employee->status)
            && ! in_array(strtolower((string) $employee->status), ['active', 'employed'], true)
        ) {
            return response()->json([
                'message' => 'This employee account is not active.',
            ], 403);
        }

        $token = $user->createToken('crow-desk-web')->plainTextToken;

        return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $this->userPayload($user, $employee),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        return response()->json([
            'user' => $this->userPayload($request->user(), $employee),
        ]);
    }

    public function dashboard(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $today = Carbon::today();

        $todayAttendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        $pendingLeaves = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'pending')
            ->count();

        $approvedLeaves = LeaveRequest::query()
            ->where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->count();

        $recentPayroll = Payroll::query()
            ->where('employee_id', $employee->id)
            ->latest('payment_date')
            ->latest('id')
            ->first();

        $canManage = (bool) $request->user()->is_super_admin
            || Employee::query()
                ->where('reporting_manager_id', $employee->id)
                ->exists();

        return response()->json([
            'employee' => $this->employeePayload($employee),
            'today' => [
                'date' => $today->toDateString(),
                'attendance' => $todayAttendance,
            ],
            'leave_summary' => [
                'pending' => $pendingLeaves,
                'approved' => $approvedLeaves,
            ],
            'latest_payroll' => $recentPayroll,
            'can_manage' => $canManage,
        ]);
    }

    public function attendance(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $query = Attendance::query()
            ->where('employee_id', $employee->id)
            ->orderByDesc('attendance_date');

        if ($request->filled('from')) {
            $query->whereDate('attendance_date', '>=', $request->date('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('attendance_date', '<=', $request->date('to'));
        }

        return response()->json([
            'data' => $query->paginate(
                min((int) $request->input('per_page', 31), 100)
            ),
        ]);
    }

    public function checkIn(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $today = Carbon::today();

        $attendance = Attendance::query()->firstOrCreate(
            [
                'employee_id' => $employee->id,
                'attendance_date' => $today->toDateString(),
            ],
            [
                'status' => 'present',
            ]
        );

        if ($attendance->check_in) {
            return response()->json([
                'message' => 'You have already checked in today.',
                'attendance' => $attendance,
            ], 422);
        }

        $now = Carbon::now();

        $attendance->check_in = $now->format('H:i:s');
        $attendance->status = $now->format('H:i:s') > '09:00:00'
            ? 'late'
            : 'present';

        if ($now->format('H:i:s') > '09:00:00') {
            $attendance->late_minutes = Carbon::createFromTimeString('09:00:00')
                ->diffInMinutes($now);
        }

        $attendance->save();

        return response()->json([
            'message' => 'Check-in recorded successfully.',
            'attendance' => $attendance,
        ]);
    }
    
    public function checkOut(Request $request): JsonResponse
    {
        $employee = $this->employee($request);
        $today = Carbon::today();

        $attendance = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $today)
            ->first();

        if (! $attendance || ! $attendance->getRawOriginal('check_in')) {
            return response()->json([
                'message' => 'You have not checked in today.',
            ], 422);
        }

        if ($attendance->getRawOriginal('check_out')) {
            return response()->json([
                'message' => 'You have already checked out today.',
                'attendance' => $attendance,
            ], 422);
        }

        // Read the original database values to avoid datetime-cast issues.
        $attendanceDate = Carbon::parse(
            $attendance->getRawOriginal('attendance_date')
        )->toDateString();

        $checkInTime = $attendance->getRawOriginal('check_in');
        $checkOutTime = Carbon::now()->format('H:i:s');

        $checkIn = Carbon::parse(
            $attendanceDate . ' ' . $checkInTime
        );

        $checkOut = Carbon::parse(
            $attendanceDate . ' ' . $checkOutTime
        );

        $attendance->check_out = $checkOutTime;

        $attendance->working_hours = round(
            max(0, $checkIn->diffInMinutes($checkOut)) / 60,
            2
        );

        $attendance->save();

        return response()->json([
            'message' => 'Check-out recorded successfully.',
            'attendance' => $attendance->fresh(),
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
                    'description',
                ]),
        ]);
    }

    public function leaves(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        return response()->json([
            'data' => LeaveRequest::query()
                ->with('leaveType')
                ->where('employee_id', $employee->id)
                ->latest('from_date')
                ->paginate(
                    min((int) $request->input('per_page', 20), 100)
                ),
        ]);
    }

    public function createLeave(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $data = $request->validate([
            'leave_type_id' => ['required', 'exists:leave_types,id'],
            'from_date' => ['required', 'date'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'],
            'reason' => ['nullable', 'string', 'max:2000'],
        ]);

        $leaveType = LeaveType::query()
            ->where('id', $data['leave_type_id'])
            ->where('is_active', true)
            ->first();

        if (! $leaveType) {
            return response()->json([
                'message' => 'Selected leave type is not active.',
            ], 422);
        }

        $from = Carbon::parse($data['from_date']);
        $to = Carbon::parse($data['to_date']);

        $days = $from->diffInDays($to) + 1;

        $leave = new LeaveRequest();
        $leave->employee_id = $employee->id;
        $leave->leave_type_id = $leaveType->id;
        $leave->from_date = $from->toDateString();
        $leave->to_date = $to->toDateString();
        $leave->days = $days;
        $leave->reason = $data['reason'] ?? null;
        $leave->status = 'pending';
        $leave->save();

        $leave->load('leaveType');

        return response()->json([
            'message' => 'Leave request submitted successfully.',
            'data' => $leave,
        ], 201);
    }

    public function cancelLeave(
        Request $request,
        LeaveRequest $leaveRequest
    ): JsonResponse {
        $employee = $this->employee($request);

        if ($leaveRequest->employee_id !== $employee->id) {
            return response()->json([
                'message' => 'You cannot cancel this leave request.',
            ], 403);
        }

        if (! in_array($leaveRequest->status, ['pending', 'approved'], true)) {
            return response()->json([
                'message' => 'This leave request cannot be cancelled.',
            ], 422);
        }

        $leaveRequest->status = 'cancelled';
        $leaveRequest->save();

        return response()->json([
            'message' => 'Leave request cancelled successfully.',
            'data' => $leaveRequest,
        ]);
    }

    public function approvals(Request $request): JsonResponse
    {
        $employee = $this->employee($request);

        $query = LeaveRequest::query()
            ->with(['employee', 'leaveType'])
            ->where('status', 'pending');

        if (! $request->user()->is_super_admin) {
            $query->whereHas('employee', function ($employeeQuery) use ($employee) {
                $employeeQuery->where('reporting_manager_id', $employee->id);
            });
        }

        return response()->json([
            'data' => $query
                ->latest()
                ->paginate(
                    min((int) $request->input('per_page', 20), 100)
                ),
        ]);
    }

    public function updateApproval(
        Request $request,
        LeaveRequest $leaveRequest
    ): JsonResponse {
        $employee = $this->employee($request);

        $canApprove = $request->user()->is_super_admin
            || $leaveRequest->employee?->reporting_manager_id === $employee->id;

        if (! $canApprove) {
            return response()->json([
                'message' => 'You are not authorized to approve this request.',
            ], 403);
        }

        $data = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'approval_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'message' => 'This leave request has already been processed.',
            ], 422);
        }

        $leaveRequest->status = $data['status'];
        $leaveRequest->approval_notes = $data['approval_notes'] ?? null;
        $leaveRequest->save();

        return response()->json([
            'message' => 'Leave request updated successfully.',
            'data' => $leaveRequest->load(['employee', 'leaveType']),
        ]);
    }

    private function userPayload($user, Employee $employee): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile ?? null,
            'is_super_admin' => (bool) $user->is_super_admin,
            'employee' => $this->employeePayload($employee),
        ];
    }

    private function employeePayload(Employee $employee): array
    {
        return [
            'id' => $employee->id,
            'employee_no' => $employee->employee_no,
            'name' => $employee->name,
            'preferred_name' => $employee->preferred_name,
            'profile_photo' => $employee->profile_photo,
            'email' => $employee->email,
            'personal_email' => $employee->personal_email,
            'phone' => $employee->phone,
            'department' => $employee->department,
            'position' => $employee->position,
            'designation' => $employee->designation,
            'job_title' => $employee->job_title,
            'status' => $employee->status,
            'employment_type' => $employee->employment_type,
            'work_location' => $employee->work_location,
            'work_mode' => $employee->work_mode,
            'join_date' => $employee->join_date,
            'basic_salary' => $employee->basic_salary,
        ];
    }
}
