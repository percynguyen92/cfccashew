<?php

declare(strict_types=1);

namespace App\Enums;

enum CuttingTestType: int
{
    case FinalFirstCut = 1;
    case FinalSecondCut = 2;
    case FinalThirdCut = 3;
    case ContainerCut = 4;

    public function label(): string
    {
        return match ($this) {
            self::FinalFirstCut => 'Final Sample - First Cut',
            self::FinalSecondCut => 'Final Sample - Second Cut',
            self::FinalThirdCut => 'Final Sample - Third Cut',
            self::ContainerCut => 'Container Cut',
        };
    }

    public function isFinalSample(): bool
    {
        return in_array($this, [self::FinalFirstCut, self::FinalSecondCut, self::FinalThirdCut]);
    }

    public function isContainerTest(): bool
    {
        return $this === self::ContainerCut;
    }

    public static function finalSampleTypes(): array
    {
        return [self::FinalFirstCut, self::FinalSecondCut, self::FinalThirdCut];
    }
}