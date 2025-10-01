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
use Illuminate\Validation\ValidationException;

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
        
        // Validate moisture levels
        $this->validateMoisture($data);
        
        // Calculate outturn rate if weights are provided
        $data = $this->calculateOutturnRate($data);
        
        return $this->cuttingTestRepository->create($data);
    }

    public function updateCuttingTest(CuttingTest $cuttingTest, array $data): bool
    {
        // Validate cutting test type and container relationship
        $this->validateCuttingTestData($data);
        
        // Validate moisture levels
        $this->validateMoisture($data);
        
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
            throw ValidationException::withMessages([
                'container_id' => 'Final sample tests cannot be associated with a container.',
            ]);
        }
        
        // Container tests (type 4) must have container_id
        if ($type->isContainerTest() && empty($data['container_id'])) {
            throw ValidationException::withMessages([
                'container_id' => 'Container tests must be associated with a container.',
            ]);
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

            $calculated = round(
                ($defectiveKernelWeight / 2 + $goodKernelWeight) * 80 / 453.6,
                2
            );

            $data['outturn_rate'] = max(0, min(60, $calculated));
        }

        return $data;
    }

    /**
     * Validate moisture levels and generate alerts for >11%
     */
    private function validateMoisture(array $data): void
    {
        if (isset($data['moisture'])) {
            $moisture = (float) $data['moisture'];
            
            if ($moisture < 0 || $moisture > 100) {
                throw ValidationException::withMessages([
                    'moisture' => 'Moisture must be between 0 and 100%.',
                ]);
            }
        }
    }

    /**
     * Get all validation alerts for a cutting test
     */
    public function getValidationAlerts(CuttingTest $cuttingTest): array
    {
        $alerts = [];

        // Alert if moisture > 11%
        if (!is_null($cuttingTest->moisture) && $cuttingTest->moisture > 11.0) {
            $alerts[] = "High moisture content: {$cuttingTest->moisture}% (exceeds 11% threshold)";
        }

        // Alert if (sample_weight - w_sample_after_cut) > 5
        if (!is_null($cuttingTest->sample_weight) && !is_null($cuttingTest->w_sample_after_cut)) {
            $sampleWeightDifference = $cuttingTest->sample_weight - $cuttingTest->w_sample_after_cut;
            if ($sampleWeightDifference > 5) {
                $alerts[] = "Sample weight discrepancy: {$sampleWeightDifference}g (exceeds 5g threshold)";
            }
        }

        // Alert if (w_defective_nut/3.3 - w_defective_kernel) > 5
        if (!is_null($cuttingTest->w_defective_nut) && !is_null($cuttingTest->w_defective_kernel)) {
            $expectedDefectiveKernel = $cuttingTest->w_defective_nut / 3.3;
            $defectiveKernelDifference = $expectedDefectiveKernel - $cuttingTest->w_defective_kernel;
            if ($defectiveKernelDifference > 5) {
                $alerts[] = "Defective kernel weight discrepancy: {$defectiveKernelDifference}g (exceeds 5g threshold)";
            }
        }

        // Alert if ((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10
        if (!is_null($cuttingTest->sample_weight) && 
            !is_null($cuttingTest->w_reject_nut) && 
            !is_null($cuttingTest->w_defective_nut) && 
            !is_null($cuttingTest->w_good_kernel)) {
            
            $expectedGoodKernel = ($cuttingTest->sample_weight - $cuttingTest->w_reject_nut - $cuttingTest->w_defective_nut) / 3.3;
            $goodKernelDifference = $expectedGoodKernel - $cuttingTest->w_good_kernel;
            if ($goodKernelDifference > 10) {
                $alerts[] = "Good kernel weight discrepancy: {$goodKernelDifference}g (exceeds 10g threshold)";
            }
        }

        // Validate outturn rate is within acceptable range
        if (!is_null($cuttingTest->outturn_rate)) {
            if ($cuttingTest->outturn_rate < 0 || $cuttingTest->outturn_rate > 60) {
                $alerts[] = "Outturn rate out of range: {$cuttingTest->outturn_rate} lbs/80kg (should be 0-60)";
            }
        }

        // Check for missing critical data (moisture is optional)
        if (is_null($cuttingTest->sample_weight)) {
            $alerts[] = 'Sample weight is missing';
        }

        return $alerts;
    }

    /**
     * Get cutting tests with validation alerts
     */
    public function getCuttingTestsWithAlerts(): Collection
    {
        $cuttingTests = $this->cuttingTestRepository->getAll();
        $testsWithAlerts = collect();

        foreach ($cuttingTests as $test) {
            $alerts = $this->getValidationAlerts($test);
            if (!empty($alerts)) {
                $test->validation_alerts = $alerts;
                $testsWithAlerts->push($test);
            }
        }

        return $testsWithAlerts;
    }

    /**
     * Get cutting tests with high moisture (>11%)
     */
    public function getCuttingTestsWithHighMoisture(): Collection
    {
        return $this->cuttingTestRepository->getAll()
            ->filter(function ($test) {
                return !is_null($test->moisture) && $test->moisture > 11.0;
            });
    }

    /**
     * Validate weight relationships in cutting test
     */
    public function validateWeightRelationships(CuttingTest $cuttingTest): array
    {
        $errors = [];

        // Check that sample_weight >= w_sample_after_cut
        if (!is_null($cuttingTest->sample_weight) && !is_null($cuttingTest->w_sample_after_cut)) {
            if ($cuttingTest->w_sample_after_cut > $cuttingTest->sample_weight) {
                $errors[] = 'Sample weight after cut cannot be greater than initial sample weight';
            }
        }

        // Check that the sum of components doesn't exceed sample weight
        if (!is_null($cuttingTest->sample_weight) && 
            !is_null($cuttingTest->w_reject_nut) && 
            !is_null($cuttingTest->w_defective_nut)) {
            
            $totalComponentWeight = $cuttingTest->w_reject_nut + $cuttingTest->w_defective_nut;
            if ($totalComponentWeight > $cuttingTest->sample_weight) {
                $errors[] = 'Sum of reject and defective nut weights cannot exceed sample weight';
            }
        }

        // Validate defective kernel weight against defective nut weight
        if (!is_null($cuttingTest->w_defective_nut) && !is_null($cuttingTest->w_defective_kernel)) {
            $maxDefectiveKernel = $cuttingTest->w_defective_nut / 2; // Assuming 50% kernel yield from defective nuts
            if ($cuttingTest->w_defective_kernel > $maxDefectiveKernel) {
                $errors[] = 'Defective kernel weight seems too high relative to defective nut weight';
            }
        }

        return $errors;
    }

    /**
     * Recalculate outturn rate with complete data validation
     */
    public function recalculateOutturnRate(CuttingTest $cuttingTest): ?float
    {
        if (is_null($cuttingTest->w_defective_kernel) || is_null($cuttingTest->w_good_kernel)) {
            return null;
        }

        $calculated = round(
            ($cuttingTest->w_defective_kernel / 2 + $cuttingTest->w_good_kernel) * 80 / 453.6,
            2
        );

        return max(0, min(60, $calculated));
    }
}
