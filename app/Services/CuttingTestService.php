<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CuttingTestType;
use App\Models\CuttingTest;
use App\Models\Bill;
use App\Models\Container;
use App\Repositories\CuttingTestRepository;
use App\Queries\CuttingTestQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CuttingTestService
{
    public function __construct(
        private CuttingTestRepository $cuttingTestRepository,
        private CuttingTestQuery $cuttingTestQuery
    ) {}

    public function getCuttingTestsWithFilters(array $filters): LengthAwarePaginator
    {
        return $this->cuttingTestRepository->findWithFilters($filters);
    }

    public function getCuttingTestById(int $id): ?CuttingTest
    {
        return $this->cuttingTestRepository->findByIdWithRelations($id, ['bill', 'container']);
    }

    public function getCuttingTestsByBillId(int $billId): Collection
    {
        return $this->cuttingTestQuery->getByBillId($billId);
    }

    public function getFinalSamplesByBillId(int $billId): Collection
    {
        return $this->cuttingTestQuery->getFinalSamplesByBillId($billId);
    }

    public function getContainerTestsByBillId(int $billId): Collection
    {
        return $this->cuttingTestQuery->getContainerTestsByBillId($billId);
    }

    public function createCuttingTest(array $data): CuttingTest
    {
        // Validate cutting test type and container relationship
        $this->validateCuttingTestData($data);
        
        // Calculate outturn rate if weights are provided
        $data = $this->calculateOutturnRate($data);
        
        return $this->cuttingTestRepository->create($data);
    }

    public function updateCuttingTest(CuttingTest $cuttingTest, array $data): bool
    {
        // Validate cutting test type and container relationship
        $this->validateCuttingTestData($data);
        
        // Calculate outturn rate if weights are provided
        $data = $this->calculateOutturnRate($data);
        
        return $this->cuttingTestRepository->update($cuttingTest, $data);
    }

    public function deleteCuttingTest(CuttingTest $cuttingTest): bool
    {
        return $this->cuttingTestRepository->delete($cuttingTest);
    }

    public function getTestsWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->cuttingTestQuery->getTestsWithHighMoisture($threshold);
    }

    public function getMoistureDistribution(): array
    {
        return $this->cuttingTestQuery->getMoistureDistribution();
    }

    public function calculateDefectiveRatio(CuttingTest $cuttingTest): ?array
    {
        if (!$cuttingTest->w_defective_nut || !$cuttingTest->w_defective_kernel) {
            return null;
        }

        $defectiveKernelRatio = $cuttingTest->w_defective_nut > 0 
            ? round($cuttingTest->w_defective_kernel / $cuttingTest->w_defective_nut, 1)
            : 0;

        return [
            'defective_nut' => $cuttingTest->w_defective_nut,
            'defective_kernel' => $cuttingTest->w_defective_kernel,
            'ratio' => $defectiveKernelRatio,
            'formatted' => "{$cuttingTest->w_defective_nut}/{$defectiveKernelRatio}"
        ];
    }

    public function isFinalSample(CuttingTest $cuttingTest): bool
    {
        $type = CuttingTestType::from($cuttingTest->type);
        return $type->isFinalSample() && is_null($cuttingTest->container_id);
    }

    public function isContainerTest(CuttingTest $cuttingTest): bool
    {
        $type = CuttingTestType::from($cuttingTest->type);
        return $type->isContainerTest() && !is_null($cuttingTest->container_id);
    }

    private function validateCuttingTestData(array $data): void
    {
        $type = CuttingTestType::from($data['type']);
        
        // Final samples (types 1-3) should not have container_id
        if ($type->isFinalSample() && !empty($data['container_id'])) {
            throw new \InvalidArgumentException('Final sample tests cannot be associated with a container.');
        }
        
        // Container tests (type 4) must have container_id
        if ($type->isContainerTest() && empty($data['container_id'])) {
            throw new \InvalidArgumentException('Container tests must be associated with a container.');
        }
    }

    public function getCuttingTestStatistics(): array
    {
        $highMoistureTests = $this->getTestsWithHighMoisture();
        $moistureDistribution = $this->getMoistureDistribution();

        return [
            'high_moisture_count' => $highMoistureTests->count(),
            'moisture_distribution' => $moistureDistribution,
            'high_moisture_tests' => $highMoistureTests,
        ];
    }

    public function getBillById(int $billId): ?Bill
    {
        return Bill::find($billId);
    }

    public function getContainerById(int $containerId): ?Container
    {
        return Container::find($containerId);
    }

    private function calculateOutturnRate(array $data): array
    {
        // Calculate outturn rate: (w_defective_kernel/2 + w_good_kernel) * 80 / 453.6
        if (!empty($data['w_defective_kernel']) && !empty($data['w_good_kernel'])) {
            $defectiveKernelWeight = (float) $data['w_defective_kernel'];
            $goodKernelWeight = (float) $data['w_good_kernel'];
            
            $data['outturn_rate'] = round(
                ($defectiveKernelWeight / 2 + $goodKernelWeight) * 80 / 453.6,
                2
            );
        }

        return $data;
    }
}