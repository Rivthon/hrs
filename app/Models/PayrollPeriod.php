<?php

namespace App\Models;

use Database\Factories\PayrollPeriodFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['period_date', 'status', 'generated_at', 'finalized_at'])]
class PayrollPeriod extends Model
{
    /** @use HasFactory<PayrollPeriodFactory> */
    use HasFactory;

    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
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
