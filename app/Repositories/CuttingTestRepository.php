<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\CuttingTestType;
use App\Models\CuttingTest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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

    public function findWithFilters(array $filters): LengthAwarePaginator
    {
        $query = $this->model->with(['bill', 'container'])
            ->when($filters['bill_number'] ?? null, function ($query, $billNumber) {
                $query->whereHas('bill', function ($q) use ($billNumber) {
                    $q->where('bill_number', 'like', "%{$billNumber}%");
                });
            })
            ->when($filters['test_type'] ?? null, function ($query, $type) {
                if ($type === 'final') {
                    $query->whereIn('type', [
                        CuttingTestType::FinalFirstCut->value,
                        CuttingTestType::FinalSecondCut->value,
                        CuttingTestType::FinalThirdCut->value,
                    ])->whereNull('container_id');
                } elseif ($type === 'container') {
                    $query->where('type', CuttingTestType::ContainerCut->value)
                        ->whereNotNull('container_id');
                } else {
                    $query->where('type', $type);
                }
            })
            ->when($filters['container_id'] ?? null, function ($query, $containerId) {
                $query->where('container_id', $containerId);
            })
            ->when($filters['moisture_min'] ?? null, function ($query, $min) {
                $query->where('moisture', '>=', $min);
            })
            ->when($filters['moisture_max'] ?? null, function ($query, $max) {
                $query->where('moisture', '<=', $max);
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            });

        $perPage = (int) ($filters['per_page'] ?? 15);
        
        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage)
                    ->withQueryString();
    }

    public function getTestsWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->where('moisture', '>', $threshold)
            ->with(['bill', 'container'])
            ->orderBy('moisture', 'desc')
            ->get();
    }

    /**
     * Get cutting tests with validation alerts based on business rules
     */
    public function getTestsWithValidationAlerts(): Collection
    {
        return $this->model
            ->whereNotNull('sample_weight')
            ->whereNotNull('w_sample_after_cut')
            ->whereNotNull('w_defective_nut')
            ->whereNotNull('w_defective_kernel')
            ->whereNotNull('w_reject_nut')
            ->whereNotNull('w_good_kernel')
            ->with(['bill', 'container'])
            ->get()
            ->filter(function ($test) {
                return $this->hasValidationAlerts($test);
            });
    }

    /**
     * Check if a cutting test has validation alerts
     */
    private function hasValidationAlerts(CuttingTest $test): bool
    {
        // Alert if (sample_weight - w_sample_after_cut) > 5
        if (($test->sample_weight - $test->w_sample_after_cut) > 5) {
            return true;
        }

        // Alert if (w_defective_nut/3.3 - w_defective_kernel) > 5
        if (($test->w_defective_nut / 3.3 - $test->w_defective_kernel) > 5) {
            return true;
        }

        // Alert if ((sample_weight - w_reject_nut - w_defective_nut)/3.3 - w_good_kernel) > 10
        $calculatedGoodKernel = ($test->sample_weight - $test->w_reject_nut - $test->w_defective_nut) / 3.3;
        if (($calculatedGoodKernel - $test->w_good_kernel) > 10) {
            return true;
        }

        return false;
    }

    /**
     * Get cutting tests by specific type with improved filtering
     */
    public function getByTypeWithFilters(int $type, array $filters = []): Collection
    {
        $query = $this->model
            ->where('type', $type)
            ->with(['bill', 'container']);

        // Filter by bill ID
        if (!empty($filters['bill_id'])) {
            $query->where('bill_id', $filters['bill_id']);
        }

        // Filter by container ID
        if (!empty($filters['container_id'])) {
            $query->where('container_id', $filters['container_id']);
        }

        // Filter by moisture range
        if (!empty($filters['moisture_min'])) {
            $query->where('moisture', '>=', $filters['moisture_min']);
        }

        if (!empty($filters['moisture_max'])) {
            $query->where('moisture', '<=', $filters['moisture_max']);
        }

        // Filter by outturn rate range
        if (!empty($filters['outturn_rate_min'])) {
            $query->where('outturn_rate', '>=', $filters['outturn_rate_min']);
        }

        if (!empty($filters['outturn_rate_max'])) {
            $query->where('outturn_rate', '<=', $filters['outturn_rate_max']);
        }

        // Filter by sample weight range
        if (!empty($filters['sample_weight_min'])) {
            $query->where('sample_weight', '>=', $filters['sample_weight_min']);
        }

        if (!empty($filters['sample_weight_max'])) {
            $query->where('sample_weight', '<=', $filters['sample_weight_max']);
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get final samples (types 1-3) with efficient joins
     */
    public function getFinalSamplesWithRelations(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->whereIn('type', [
                CuttingTestType::FinalFirstCut->value,
                CuttingTestType::FinalSecondCut->value,
                CuttingTestType::FinalThirdCut->value
            ])
            ->whereNull('container_id')
            ->with([
                'bill' => function ($query) {
                    $query->select('id', 'bill_number', 'seller', 'buyer', 'origin');
                }
            ])
            ->orderBy('type')
            ->get();
    }

    /**
     * Get container tests (type 4) with efficient joins
     */
    public function getContainerTestsWithRelations(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->where('type', CuttingTestType::ContainerCut->value)
            ->whereNotNull('container_id')
            ->with([
                'bill' => function ($query) {
                    $query->select('id', 'bill_number', 'seller', 'buyer', 'origin');
                },
                'container' => function ($query) {
                    $query->select('id', 'container_number', 'truck', 'quantity_of_bags', 'w_net', 'container_condition', 'seal_condition');
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Enhanced filter method with validation alert detection
     */
    public function findWithFiltersAndAlerts(array $filters): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->with(['bill', 'container'])
            ->when($filters['bill_number'] ?? null, function ($query, $billNumber) {
                $query->whereHas('bill', function ($q) use ($billNumber) {
                    $q->where('bill_number', 'like', "%{$billNumber}%");
                });
            })
            ->when($filters['container_number'] ?? null, function ($query, $containerNumber) {
                $query->whereHas('container', function ($q) use ($containerNumber) {
                    $q->where('container_number', 'like', "%{$containerNumber}%");
                });
            })
            ->when($filters['test_type'] ?? null, function ($query, $type) {
                if ($type === 'final') {
                    $query->whereIn('type', [
                        CuttingTestType::FinalFirstCut->value,
                        CuttingTestType::FinalSecondCut->value,
                        CuttingTestType::FinalThirdCut->value,
                    ])->whereNull('container_id');
                } elseif ($type === 'container') {
                    $query->where('type', CuttingTestType::ContainerCut->value)
                        ->whereNotNull('container_id');
                } else {
                    $query->where('type', $type);
                }
            })
            ->when($filters['container_id'] ?? null, function ($query, $containerId) {
                $query->where('container_id', $containerId);
            })
            ->when($filters['bill_id'] ?? null, function ($query, $billId) {
                $query->where('bill_id', $billId);
            })
            ->when($filters['moisture_min'] ?? null, function ($query, $min) {
                $query->where('moisture', '>=', $min);
            })
            ->when($filters['moisture_max'] ?? null, function ($query, $max) {
                $query->where('moisture', '<=', $max);
            })
            ->when($filters['outturn_rate_min'] ?? null, function ($query, $min) {
                $query->where('outturn_rate', '>=', $min);
            })
            ->when($filters['outturn_rate_max'] ?? null, function ($query, $max) {
                $query->where('outturn_rate', '<=', $max);
            })
            ->when($filters['sample_weight_min'] ?? null, function ($query, $min) {
                $query->where('sample_weight', '>=', $min);
            })
            ->when($filters['sample_weight_max'] ?? null, function ($query, $max) {
                $query->where('sample_weight', '<=', $max);
            })
            ->when($filters['date_from'] ?? null, function ($query, $dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            })
            ->when($filters['date_to'] ?? null, function ($query, $dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            })
            ->when($filters['has_validation_alerts'] ?? null, function ($query, $hasAlerts) {
                if ($hasAlerts) {
                    $query->whereNotNull('sample_weight')
                        ->whereNotNull('w_sample_after_cut')
                        ->whereNotNull('w_defective_nut')
                        ->whereNotNull('w_defective_kernel')
                        ->whereNotNull('w_reject_nut')
                        ->whereNotNull('w_good_kernel');
                }
            });

        $perPage = (int) ($filters['per_page'] ?? 15);
        
        $results = $query->orderBy('created_at', 'desc')
                        ->paginate($perPage)
                        ->withQueryString();

        // If filtering by validation alerts, filter the collection
        if (!empty($filters['has_validation_alerts'])) {
            $results->getCollection()->transform(function ($test) {
                $test->has_validation_alerts = $this->hasValidationAlerts($test);
                return $test;
            });

            if ($filters['has_validation_alerts']) {
                $filteredItems = $results->getCollection()->filter(function ($test) {
                    return $test->has_validation_alerts;
                });
                $results->setCollection($filteredItems);
            }
        }

        return $results;
    }

    /**
     * Get cutting tests with specific validation alert types
     */
    public function getTestsByValidationAlertType(string $alertType): Collection
    {
        return $this->model
            ->whereNotNull('sample_weight')
            ->whereNotNull('w_sample_after_cut')
            ->whereNotNull('w_defective_nut')
            ->whereNotNull('w_defective_kernel')
            ->whereNotNull('w_reject_nut')
            ->whereNotNull('w_good_kernel')
            ->with(['bill', 'container'])
            ->get()
            ->filter(function ($test) use ($alertType) {
                switch ($alertType) {
                    case 'sample_weight_discrepancy':
                        return ($test->sample_weight - $test->w_sample_after_cut) > 5;
                    case 'defective_kernel_discrepancy':
                        return ($test->w_defective_nut / 3.3 - $test->w_defective_kernel) > 5;
                    case 'good_kernel_discrepancy':
                        $calculatedGoodKernel = ($test->sample_weight - $test->w_reject_nut - $test->w_defective_nut) / 3.3;
                        return ($calculatedGoodKernel - $test->w_good_kernel) > 10;
                    default:
                        return false;
                }
            });
    }

    /**
     * Get cutting tests with efficient bill and container joins for reporting
     */
    public function getTestsForReporting(array $filters = []): Collection
    {
        $query = $this->model
            ->select([
                'cutting_tests.*',
                'bills.bill_number',
                'bills.seller',
                'bills.buyer',
                'bills.origin',
                'containers.container_number',
                'containers.truck'
            ])
            ->join('bills', 'cutting_tests.bill_id', '=', 'bills.id')
            ->leftJoin('containers', 'cutting_tests.container_id', '=', 'containers.id');

        // Apply filters
        if (!empty($filters['bill_id'])) {
            $query->where('cutting_tests.bill_id', $filters['bill_id']);
        }

        if (!empty($filters['type'])) {
            $query->where('cutting_tests.type', $filters['type']);
        }

        if (!empty($filters['moisture_threshold'])) {
            $query->where('cutting_tests.moisture', '>', $filters['moisture_threshold']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('cutting_tests.created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('cutting_tests.created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('cutting_tests.created_at', 'desc')->get();
    }
}
