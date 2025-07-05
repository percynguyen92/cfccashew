<?php

declare(strict_types=1);

namespace Tests\Unit\Enums;

use App\Enums\CuttingTestType;
use PHPUnit\Framework\TestCase;

final class CuttingTestTypeTest extends TestCase
{
    public function test_enum_has_expected_values(): void
    {
        $this->assertSame(1, CuttingTestType::FINAL_SAMPLE_FIRST_CUT->value);
        $this->assertSame(2, CuttingTestType::FINAL_SAMPLE_SECOND_CUT->value);
        $this->assertSame(3, CuttingTestType::FINAL_SAMPLE_THIRD_CUT->value);
        $this->assertSame(4, CuttingTestType::CONTAINER_CUT->value);
    }

    public function test_enum_can_be_instantiated_from_value(): void
    {
        $this->assertSame(
            CuttingTestType::FINAL_SAMPLE_FIRST_CUT,
            CuttingTestType::from(1)
        );
    }

    public function test_enum_name_is_correct(): void
    {
        $this->assertSame('FINAL_SAMPLE_FIRST_CUT', CuttingTestType::from(1)->name);
    }

    public function test_enum_to_array(): void
    {
        $enumValues = array_column(CuttingTestType::cases(), 'value');
        $this->assertSame([1, 2, 3, 4], $enumValues);
    }
}
