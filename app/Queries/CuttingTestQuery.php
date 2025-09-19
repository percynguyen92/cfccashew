<?php

declare(strict_types=1);

namespace App\Queries;

use App\Enums\CuttingTestType;
use App\Models\CuttingTest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CuttingTestQuery
{
    public function __construct(
        private CuttingTest $model
    ) {}

    public function getByBillId(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->with(['container'])
            ->orderBy('type')
            ->orderBy('created_at')
            ->get();
    }

    public function getFinalSamplesByBillId(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->whereIn('type', [
                CuttingTestType::FinalFirstCut->value,
                CuttingTestType::FinalSecondCut->value,
                CuttingTestType::FinalThirdCut->value
            ])
            ->whereNull('container_id')
            ->orderBy('type')
            ->get();
    }

    public function getContainerTestsByBillId(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->where('type', CuttingTestType::ContainerCut->value)
            ->whereNotNull('container_id')
            ->with('container')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getTestsWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->where('moisture', '>', $threshold)
            ->with(['bill:id,bill_number,seller,buyer', 'container:id,container_number,truck'])
            ->orderBy('moisture', 'desc')
            ->get();
    }

    public function getMoistureDistribution(): array
    {
        $tests = $this->model
            ->whereNotNull('moisture')
            ->selectRaw('
                COUNT(*) as total_tests,
                AVG(moisture) as avg_moisture,
                MIN(moisture) as min_moisture,
                MAX(moisture) as max_moisture,
                SUM(CASE WHEN moisture <= 8 THEN 1 ELSE 0 END) as low_moisture,
                SUM(CASE WHEN moisture > 8 AND moisture <= 11 THEN 1 ELSE 0 END) as medium_moisture,
                SUM(CASE WHEN moisture > 11 THEN 1 ELSE 0 END) as high_moisture
            ')
            ->first();

        return [
            'total_tests' => $tests->total_tests ?? 0,
            'avg_moisture' => $tests->avg_moisture ? round($tests->avg_moisture, 1) : null,
            'min_moisture' => $tests->min_moisture,
            'max_moisture' => $tests->max_moisture,
            'distribution' => [
                'low' => $tests->low_moisture ?? 0,      // <= 8%
                'medium' => $tests->medium_moisture ?? 0, // 8-11%
                'high' => $tests->high_moisture ?? 0,     // > 11%
            ]
        ];
    }

    public function search(array $filters = []): Builder
    {
        $query = $this->model->query()->with(['bill', 'container']);

        if (!empty($filters['bill_id'])) {
            $query->where('bill_id', $filters['bill_id']);
        }

        if (!empty($filters['container_id'])) {
            $query->where('container_id', $filters['container_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['moisture_min'])) {
            $query->where('moisture', '>=', $filters['moisture_min']);
        }

        if (isset($filters['moisture_max'])) {
            $query->where('moisture', '<=', $filters['moisture_max']);
        }

        return $query;
    }
}