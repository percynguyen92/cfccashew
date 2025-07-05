<?php

declare(strict_types=1);

namespace App\Enums;

enum CuttingTestType: int
{
    case FINAL_SAMPLE_FIRST_CUT = 1;
    case FINAL_SAMPLE_SECOND_CUT = 2;
    case FINAL_SAMPLE_THIRD_CUT = 3;
    case CONTAINER_CUT = 4;
}