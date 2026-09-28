<?php

namespace App\Enums;

enum LeaveRequestStatus: string
{
    case PendingSupervisor = 'pending_supervisor';
    case PendingHr = 'pending_hr';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::PendingSupervisor => 'Menunggu Atasan',
            self::PendingHr => 'Menunggu SDM',
            self::Approved => 'Disetujui',
            self::Rejected => 'Ditolak',
        };
    }
}
