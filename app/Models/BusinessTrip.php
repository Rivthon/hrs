<?php

namespace App\Models;

use Database\Factories\BusinessTripFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'employee_id', 'assigned_by_user_id', 'title', 'destination', 'purpose', 'start_date', 'end_date',
    'transportation', 'allowance', 'assignment_notes', 'status', 'responded_at', 'rejection_reason',
    'report_summary', 'report_result', 'report_notes', 'report_image_path', 'reported_at',
])]
class BusinessTrip extends Model
{
    /** @use HasFactory<BusinessTripFactory> */
    use HasFactory;

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by_user_id');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'assigned' => 'Menunggu Penerimaan',
            'accepted' => 'Diterima',
            'rejected' => 'Ditolak',
            'reported' => 'Laporan Selesai',
            default => ucfirst($this->status),
        };
    }

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'allowance' => 'decimal:2',
            'responded_at' => 'datetime',
            'reported_at' => 'datetime',
        ];
    }
}
