<?php

declare(strict_types=1);

namespace App\Enums;

enum ContainerCondition: string
{
    case Intact = 'Nguyên vẹn';
    case Damaged = 'Hư hỏng';
    case Slightly_Damaged = 'Hư hỏng nhẹ';
    case Severely_Damaged = 'Hư hỏng nặng';

    public function label(): string
    {
        return match ($this) {
            self::Intact => 'Intact',
            self::Damaged => 'Damaged',
            self::Slightly_Damaged => 'Slightly Damaged',
            self::Severely_Damaged => 'Severely Damaged',
        };
    }

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}