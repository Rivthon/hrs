<?php

namespace App\Models;

use Database\Factories\PayrollPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

#[Fillable(['period_date', 'status', 'generated_at', 'finalized_at'])]
class PayrollPeriod extends Model
{
    /** @use HasFactory<PayrollPeriodFactory> */
    use HasFactory;

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    protected static function booted(): void
    {
        static::updating(function (PayrollPeriod $period): void {
            if ($period->getOriginal('status') === 'finalized') {
                throw new LogicException('Periode payroll yang sudah final tidak dapat diubah.');
            }
        });

        static::deleting(function (PayrollPeriod $period): void {
            if ($period->status === 'finalized') {
                throw new LogicException('Periode payroll yang sudah final tidak dapat dihapus.');
            }
        });
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'period_date' => 'date',
            'generated_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }
}
