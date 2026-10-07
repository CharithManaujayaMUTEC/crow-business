<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">

    <style>
        @page {
            margin: 12mm;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #222;
        }

        .header {
            width: 100%;
            margin-bottom: 25px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
        }

        .logo {
            width: 42mm;
            height: auto;
        }

        .company {
            text-align: right;
            font-size: 11px;
            line-height: 1.5;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin: 20px 0;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 18px;
            margin-bottom: 8px;
            border-bottom: 1px solid #999;
            padding-bottom: 4px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .details td {
            padding: 5px 7px;
            border: 1px solid #ddd;
        }

        .details .label {
            width: 30%;
            font-weight: bold;
        }

        .salary td,
        .salary th {
            padding: 7px;
            border: 1px solid #ccc;
        }

        .salary th {
            font-weight: bold;
            text-align: left;
        }

        .amount {
            text-align: right;
        }

        .total td {
            font-weight: bold;
            font-size: 12px;
        }

        .net {
            margin-top: 18px;
            border: 2px solid #333;
            padding: 12px;
            font-size: 15px;
            font-weight: bold;
        }

        .footer {
            margin-top: 35px;
            font-size: 9px;
            text-align: center;
            color: #666;
        }
    </style>
</head>

<body>

@php
    $company = \App\Models\CompanySetting::query()->first();

    $logoPath = public_path('images/crow-logo.png');
    $logoBase64 = null;

    if (is_file($logoPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
    }

    $employee = $payroll->employee;
@endphp

<div class="header">
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo">
                @endif
            </td>

            <td class="company">
                <strong>{{ $company?->company_name ?? 'Crow Business' }}</strong><br>

                @if($company?->address)
                    {{ $company->address }}<br>
                @endif

                @if($company?->phone)
                    {{ $company->phone }}<br>
                @endif

                @if($company?->email)
                    {{ $company->email }}
                @endif
            </td>
        </tr>
    </table>
</div>

<div class="title">
    PAYSLIP
</div>

<div class="section-title">
    Employee Details
</div>

<table class="details">
    <tr>
        <td class="label">Employee</td>
        <td>{{ $employee?->name ?? 'N/A' }}</td>
    </tr>

    <tr>
        <td class="label">Employee No.</td>
        <td>{{ $employee?->employee_no ?? 'N/A' }}</td>
    </tr>

    <tr>
        <td class="label">Payroll Period</td>
        <td>{{ $payroll->period }}</td>
    </tr>

    <tr>
        <td class="label">Payment Date</td>
        <td>{{ $payroll->payment_date?->format('Y-m-d') ?? 'N/A' }}</td>
    </tr>
</table>

<div class="section-title">
    Earnings
</div>

<table class="salary">
    <thead>
        <tr>
            <th>Description</th>
            <th class="amount">Amount (LKR)</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>Basic Salary</td>
            <td class="amount">{{ number_format((float) $payroll->basic_salary, 2) }}</td>
        </tr>

        <tr>
            <td>Allowance</td>
            <td class="amount">{{ number_format((float) $payroll->allowance, 2) }}</td>
        </tr>

        <tr>
            <td>Fixed Allowance</td>
            <td class="amount">{{ number_format((float) $payroll->fixed_allowance, 2) }}</td>
        </tr>

        <tr>
            <td>Other Allowances</td>
            <td class="amount">{{ number_format((float) $payroll->other_allowances, 2) }}</td>
        </tr>

        <tr>
            <td>Overtime</td>
            <td class="amount">{{ number_format((float) $payroll->overtime_amount, 2) }}</td>
        </tr>

        <tr class="total">
            <td>Gross Salary</td>
            <td class="amount">{{ number_format((float) $payroll->gross_salary, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="section-title">
    Attendance
</div>

<table class="details">
    <tr>
        <td class="label">Working Days</td>
        <td>{{ $payroll->working_days }}</td>
    </tr>

    <tr>
        <td class="label">Present Days</td>
        <td>{{ $payroll->present_days }}</td>
    </tr>

    <tr>
        <td class="label">Absent Days</td>
        <td>{{ $payroll->absent_days }}</td>
    </tr>

    <tr>
        <td class="label">No-Pay Days</td>
        <td>{{ number_format((float) $payroll->no_pay_days, 2) }}</td>
    </tr>
</table>

<div class="section-title">
    Deductions
</div>

<table class="salary">
    <thead>
        <tr>
            <th>Description</th>
            <th class="amount">Amount (LKR)</th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>EPF - Employee</td>
            <td class="amount">{{ number_format((float) $payroll->epf_employee, 2) }}</td>
        </tr>

        <tr>
            <td>Other Deduction</td>
            <td class="amount">{{ number_format((float) $payroll->deduction, 2) }}</td>
        </tr>

        <tr class="total">
            <td>Total Deductions</td>
            <td class="amount">{{ number_format((float) $payroll->total_deductions, 2) }}</td>
        </tr>
    </tbody>
</table>

<div class="net">
    NET SALARY:
    <span style="float:right;">
        LKR {{ number_format((float) $payroll->net_salary, 2) }}
    </span>
</div>

<div class="section-title">
    Employer Contributions
</div>

<table class="details">
    <tr>
        <td class="label">EPF - Employer</td>
        <td class="amount">LKR {{ number_format((float) $payroll->epf_employer, 2) }}</td>
    </tr>

    <tr>
        <td class="label">ETF - Employer</td>
        <td class="amount">LKR {{ number_format((float) $payroll->etf_employer, 2) }}</td>
    </tr>
</table>

@if($payroll->notes)
    <div class="section-title">
        Notes
    </div>

    <div>
        {{ $payroll->notes }}
    </div>
@endif

<div class="footer">
    This is a computer-generated payslip.
</div>

</body>
</html>
