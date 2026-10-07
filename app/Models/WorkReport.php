<?php

namespace App\Models;

use Database\Factories\WorkReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['employee_id', 'report_date', 'title', 'description', 'status'])]
class WorkReport extends Model
{
    /** @use HasFactory<WorkReportFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['report_date' => 'date'];
    }
}
