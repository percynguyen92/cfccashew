<?php

declare(strict_types=1);

namespace App\Enums;

enum SealCondition: string
{
    case Intact = 'Nguyên vẹn';
    case Broken = 'Bị phá';
    case Missing = 'Thiếu';
    case Tampered = 'Bị can thiệp';

    public function label(): string
    {
        return match ($this) {
            self::Intact => 'Intact',
            self::Broken => 'Broken',
            self::Missing => 'Missing',
            self::Tampered => 'Tampered',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}