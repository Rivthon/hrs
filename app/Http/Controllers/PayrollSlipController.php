<?php

namespace App\Http\Controllers;

use App\Actions\GeneratePayrollSlipPdfAction;
use App\Models\Payroll;
use Illuminate\Http\Response;

class PayrollSlipController extends Controller
{
    public function __invoke(Payroll $payroll, GeneratePayrollSlipPdfAction $generatePdf): Response
    {
        return response($generatePdf->handle($payroll), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="'.$generatePdf->filename($payroll).'"',
        ]);
    }
}
