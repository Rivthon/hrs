<?php

namespace App\Enums;

enum LeaveType: string
{
    case Annual = 'annual';
    case Sick = 'sick';
    case Maternity = 'maternity';
    case SpecialMarriage = 'special_marriage';
    case SpecialBereavement = 'special_bereavement';
    case HalfDay = 'half_day';
    case Hourly = 'hourly';

    public function label(): string
    {
        return match ($this) {
            self::Annual => 'Cuti Tahunan',
            self::Sick => 'Cuti Sakit',
            self::Maternity => 'Cuti Melahirkan',
            self::SpecialMarriage => 'Izin Khusus - Menikah',
            self::SpecialBereavement => 'Izin Khusus - Duka',
            self::HalfDay => 'Izin Setengah Hari',
            self::Hourly => 'Izin Per Jam',
        };
    }

    public function isPartialDay(): bool
    {
        return in_array($this, [self::HalfDay, self::Hourly], true);
    }
}
