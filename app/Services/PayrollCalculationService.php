<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\CompanySetting;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class PayrollCalculationService
{
    /**
     * Calculate payroll values for an employee and payroll period.
     *
     * EPF/ETF rates:
     * - Employee EPF: 8%
     * - Employer EPF: 12%
     * - Employer ETF: 3%
     *
     * These rates are kept in one place so they can later
     * be moved into Company Settings.
     */
    public function calculate(
        Employee $employee,
        string $period,
        float $overtimeAmount = 0,
        float $otherDeduction = 0,
    ): array {
        [$startDate, $endDate] = $this->periodDates($period);

        $attendances = Attendance::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();

        $workingDays = $this->workingDays($startDate, $endDate);

        $presentDays = $attendances->whereIn('status', [
            'present',
            'late',
        ])->count();

        $halfDays = $attendances->where('status', 'half_day')->count();

        $absentDays = $attendances->where('status', 'absent')->count();

        $leaveDays = $attendances->where('status', 'leave')->count();

        $holidayDays = $attendances->where('status', 'holiday')->count();

        /*
         * A half-day contributes 0.5 day.
         * Leave and holidays are not treated as no-pay days.
         * Absences become no-pay days.
         */
        $paidAttendanceDays =
            $presentDays +
            ($halfDays * 0.5) +
            $leaveDays +
            $holidayDays;

        $noPayDays = max(
            0,
            $workingDays - $paidAttendanceDays
        );

        /*
         * Use employee salary configuration as the source.
         */
        $basicSalary = (float) ($employee->basic_salary ?? 0);
        $allowance = (float) ($employee->allowance ?? 0);
        $fixedAllowance = (float) ($employee->fixed_allowance ?? 0);
        $otherAllowances = (float) ($employee->other_allowances ?? 0);

        /*
         * No-pay deduction is calculated against basic salary.
         */
        $dailyRate = $workingDays > 0
            ? $basicSalary / $workingDays
            : 0;

        $noPayDeduction = $dailyRate * $noPayDays;

        /*
         * Gross salary before employee EPF.
         */
        $grossSalary =
            $basicSalary +
            $allowance +
            $fixedAllowance +
            $otherAllowances +
            $overtimeAmount -
            $noPayDeduction;

        $grossSalary = max(0, $grossSalary);

        /*
         * EPF/ETF are calculated against gross salary.
         */
        $companySettings = CompanySetting::query()->first();

        $epfEmployeeRate = (float) ($companySettings?->epf_employee_rate ?? 8.00);
        $epfEmployerRate = (float) ($companySettings?->epf_employer_rate ?? 12.00);
        $etfEmployerRate = (float) ($companySettings?->etf_employer_rate ?? 3.00);

        $epfEmployee = $grossSalary * ($epfEmployeeRate / 100);
        $epfEmployer = $grossSalary * ($epfEmployerRate / 100);
        $etfEmployer = $grossSalary * ($etfEmployerRate / 100);
        /*
         * Standard employee deduction from profile +
         * manually supplied payroll deduction.
         */
        $standardDeduction = (float) ($employee->standard_deduction ?? 0);

        $totalDeductions =
            $epfEmployee +
            $standardDeduction +
            $otherDeduction;

        $netSalary = max(
            0,
            $grossSalary - $totalDeductions
        );

        return [
            'basic_salary' => round($basicSalary, 2),
            'allowance' => round($allowance, 2),
            'fixed_allowance' => round($fixedAllowance, 2),
            'other_allowances' => round($otherAllowances, 2),
            'overtime_amount' => round($overtimeAmount, 2),

            'gross_salary' => round($grossSalary, 2),

            'working_days' => $workingDays,
            'present_days' => $presentDays,
            'absent_days' => $absentDays,
            'no_pay_days' => round($noPayDays, 2),

            'epf_employee' => round($epfEmployee, 2),
            'epf_employer' => round($epfEmployer, 2),
            'etf_employer' => round($etfEmployer, 2),

            'deduction' => round($otherDeduction, 2),
            'total_deductions' => round($totalDeductions, 2),

            'net_salary' => round($netSalary, 2),
        ];
    }

    private function periodDates(string $period): array
    {
        $start = Carbon::createFromFormat('Y-m', $period)
            ->startOfMonth();

        $end = $start->copy()->endOfMonth();

        return [$start, $end];
    }

    private function workingDays(
        Carbon $startDate,
        Carbon $endDate
    ): int {
        $days = 0;

        foreach (CarbonPeriod::create($startDate, $endDate) as $date) {
            if (!$date->isWeekend()) {
                $days++;
            }
        }

        return $days;
    }
}