<?php

namespace App\Models;

use Database\Factories\PayrollFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

#[Fillable([
    'payroll_period_id', 'employee_id', 'base_salary', 'transport_allowance', 'position_allowance',
    'functional_allowance', 'teaching_honor', 'proctoring_honor', 'final_seminar_honor',
    'thesis_defense_honor', 'thesis_supervisor_honor', 'pkk_supervision_honor',
    'practical_exam_honor', 'duty_honor', 'bpjs_employment_deduction', 'bpjs_health_deduction',
    'income_tax_deduction', 'transport_deduction', 'lateness_deduction', 'gross_income',
    'total_deductions', 'net_salary', 'notes',
])]
class Payroll extends Model
{
    /** @use HasFactory<PayrollFactory> */
    use HasFactory;

    public const EARNING_FIELDS = [
        'base_salary', 'transport_allowance', 'position_allowance', 'functional_allowance',
        'teaching_honor', 'proctoring_honor', 'final_seminar_honor', 'thesis_defense_honor',
        'thesis_supervisor_honor', 'pkk_supervision_honor', 'practical_exam_honor', 'duty_honor',
    ];

    public const DEDUCTION_FIELDS = [
        'bpjs_employment_deduction', 'bpjs_health_deduction', 'income_tax_deduction',
        'transport_deduction', 'lateness_deduction',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(PayrollPeriod::class, 'payroll_period_id');
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    protected static function booted(): void
    {
        $ensureDraft = function (Payroll $payroll): void {
            if ($payroll->period()->where('status', 'finalized')->exists()) {
                throw new LogicException('Payroll yang sudah final tidak dapat diubah.');
            }
        };

        static::updating($ensureDraft);
        static::deleting($ensureDraft);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return collect([...self::EARNING_FIELDS, ...self::DEDUCTION_FIELDS, 'gross_income', 'total_deductions', 'net_salary'])
            ->mapWithKeys(fn (string $field): array => [$field => 'decimal:2'])
            ->all();
    }
}
