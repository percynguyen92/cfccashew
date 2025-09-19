<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\CuttingTestType;
use App\Models\CuttingTest;
use Illuminate\Database\Eloquent\Collection;

class CuttingTestRepository
{
    public function __construct(
        private CuttingTest $model
    ) {}

    public function findById(int $id): ?CuttingTest
    {
        return $this->model->find($id);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?CuttingTest
    {
        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): CuttingTest
    {
        return $this->model->create($data);
    }

    public function update(CuttingTest $cuttingTest, array $data): bool
    {
        return $cuttingTest->update($data);
    }

    public function delete(CuttingTest $cuttingTest): bool
    {
        return $cuttingTest->delete();
    }

    public function getByBillId(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->with(['container'])
            ->orderBy('type')
            ->get();
    }

    public function getByContainerId(int $containerId): Collection
    {
        return $this->model
            ->where('container_id', $containerId)
            ->orderBy('created_at', 'desc')
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
            ->get();
    }

    public function getTestsWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->where('moisture', '>', $threshold)
            ->with(['bill', 'container'])
            ->orderBy('moisture', 'desc')
            ->get();
    }
}