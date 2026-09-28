<?php

namespace App\Jobs;

use App\Actions\GeneratePayrollSlipPdfAction;
use App\Mail\PayrollSlipMail;
use App\Models\Payroll;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendPayrollSlipEmail implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 600;

    /** @var array<int, int> */
    public array $backoff = [60, 300, 900];

    public function __construct(public Payroll $payroll) {}

    public function handle(GeneratePayrollSlipPdfAction $generatePdf): void
    {
        $this->payroll->loadMissing(['employee.position', 'employee.user', 'period']);

        if (blank($this->payroll->employee->email)) {
            return;
        }

        Mail::to($this->payroll->employee->email)->send(new PayrollSlipMail(
            $this->payroll,
            $generatePdf->handle($this->payroll),
            $generatePdf->filename($this->payroll),
        ));
    }

    public function uniqueId(): string
    {
        return (string) $this->payroll->getKey();
    }
}
