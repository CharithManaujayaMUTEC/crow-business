<?php

namespace App\Http\Controllers;

use App\Models\Payroll;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PayslipController extends Controller
{
    public function show(Payroll $payroll): Response
    {
        $payroll->load('employee');

        return Pdf::loadView('pdf.payslip', [
            'payroll' => $payroll,
        ])
            ->setPaper('a4')
            ->stream('payslip-' . $payroll->id . '.pdf');
    }
}
