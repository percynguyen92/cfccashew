<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Repositories\BillRepository;
use App\Queries\BillQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class BillService
{
    public function __construct(
        private BillRepository $billRepository,
        private BillQuery $billQuery
    ) {}

    public function getAllBills(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->billQuery->paginate($filters, $perPage);
    }

    public function getBillById(int $id): ?Bill
    {
        return $this->billRepository->findByIdWithRelations($id, [
            'containers' => function ($query) {
                $query->orderBy('id')
                    ->with('cuttingTests');
            },
            'finalSamples' => function ($query) {
                $query->orderBy('type');
            },
        ]);
    }

    public function createBill(array $data): Bill
    {
        // Validate Bill-specific business rules
        $this->validateBillData($data);
        
        // Process inspection dates
        $data = $this->processInspectionDates($data);
        
        return $this->billRepository->create($data);
    }

    public function updateBill(Bill $bill, array $data): bool
    {
        // Validate Bill-specific business rules
        $this->validateBillData($data);
        
        // Process inspection dates
        $data = $this->processInspectionDates($data);
        
        return $this->billRepository->update($bill, $data);
    }

    public function deleteBill(Bill $bill): bool
    {
        return $this->billRepository->delete($bill);
    }

    public function getRecentBills(int $limit = 10): Collection
    {
        return $this->billQuery->getRecentBills($limit);
    }

    public function getBillsPendingFinalTests(): Collection
    {
        return $this->billQuery->getBillsPendingFinalTests();
    }

    public function getBillsMissingFinalSamples(): Collection
    {
        return $this->billQuery->getBillsMissingFinalSamples();
    }

    public function calculateAverageOutturn(Bill $bill): ?float
    {
        $finalSamples = $bill->finalSamples()->whereNotNull('outturn_rate')->get();
        
        if ($finalSamples->isEmpty()) {
            return null;
        }

        return round($finalSamples->avg('outturn_rate'), 2);
    }

    public function getBillStatistics(): array
    {
        $totalBills = $this->billRepository->getAll()->count();
        $recentBills = $this->getRecentBills(5);
        $pendingFinalTests = $this->getBillsPendingFinalTests();
        $missingFinalSamples = $this->getBillsMissingFinalSamples();

        return [
            'total_bills' => $totalBills,
            'recent_bills' => $recentBills,
            'pending_final_tests_count' => $pendingFinalTests->count(),
            'missing_final_samples_count' => $missingFinalSamples->count(),
            'pending_final_tests' => $pendingFinalTests,
            'missing_final_samples' => $missingFinalSamples,
        ];
    }

    /**
     * Validate Bill-specific business rules
     */
    private function validateBillData(array $data): void
    {
        $errors = [];

        // Validate inspection dates logic
        if (isset($data['inspection_start_date']) && isset($data['inspection_end_date'])) {
            $startDate = Carbon::parse($data['inspection_start_date']);
            $endDate = Carbon::parse($data['inspection_end_date']);
            
            if ($endDate->lt($startDate)) {
                $errors['inspection_end_date'] = 'Inspection end date must be after start date.';
            }
            
            // Ensure inspection dates are not in the future
            if ($startDate->gt(Carbon::now())) {
                $errors['inspection_start_date'] = 'Inspection start date cannot be in the future.';
            }
            
            if ($endDate->gt(Carbon::now())) {
                $errors['inspection_end_date'] = 'Inspection end date cannot be in the future.';
            }
        }

        // Validate sampling ratio
        if (isset($data['sampling_ratio'])) {
            $samplingRatio = (float) $data['sampling_ratio'];
            if ($samplingRatio <= 0 || $samplingRatio > 100) {
                $errors['sampling_ratio'] = 'Sampling ratio must be between 0.01 and 100.';
            }
        }

        // Validate weight fields
        if (isset($data['w_dunnage_dribag']) && $data['w_dunnage_dribag'] < 0) {
            $errors['w_dunnage_dribag'] = 'Dunnage/dribag weight cannot be negative.';
        }

        if (isset($data['w_jute_bag']) && $data['w_jute_bag'] <= 0) {
            $errors['w_jute_bag'] = 'Jute bag weight must be greater than 0.';
        }

        if (isset($data['net_on_bl']) && $data['net_on_bl'] <= 0) {
            $errors['net_on_bl'] = 'Net weight on BL must be greater than 0.';
        }

        if (isset($data['quantity_of_bags_on_bl']) && $data['quantity_of_bags_on_bl'] <= 0) {
            $errors['quantity_of_bags_on_bl'] = 'Quantity of bags on BL must be greater than 0.';
        }

        if (!empty($errors)) {
            throw ValidationException::withMessages($errors);
        }
    }

    /**
     * Process inspection dates to ensure proper formatting
     */
    private function processInspectionDates(array $data): array
    {
        if (isset($data['inspection_start_date'])) {
            $data['inspection_start_date'] = Carbon::parse($data['inspection_start_date'])->format('Y-m-d H:i:s');
        }

        if (isset($data['inspection_end_date'])) {
            $data['inspection_end_date'] = Carbon::parse($data['inspection_end_date'])->format('Y-m-d H:i:s');
        }

        return $data;
    }

    /**
     * Coordinate weight calculations with associated containers
     */
    public function updateContainerWeights(Bill $bill): void
    {
        foreach ($bill->containers as $container) {
            // Trigger recalculation of container weights when Bill weight fields change
            $container->w_tare = $container->calculateTareWeight();
            $container->w_net = $container->calculateNetWeight();
            $container->save();
        }
    }

    /**
     * Get bills by origin
     */
    public function getBillsByOrigin(string $origin): Collection
    {
        return $this->billRepository->getAll()->where('origin', $origin);
    }

    /**
     * Get bills by inspection date range
     */
    public function getBillsByInspectionDateRange(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->billRepository->getAll()
            ->whereBetween('inspection_start_date', [$startDate, $endDate])
            ->orWhereBetween('inspection_end_date', [$startDate, $endDate]);
    }

    /**
     * Get bills by sampling ratio range
     */
    public function getBillsBySamplingRatio(float $minRatio, float $maxRatio): Collection
    {
        return $this->billRepository->getAll()
            ->whereBetween('sampling_ratio', [$minRatio, $maxRatio]);
    }

    /**
     * Validate that all required fields for weight calculations are present
     */
    public function validateWeightCalculationFields(Bill $bill): array
    {
        $missingFields = [];

        if (is_null($bill->w_jute_bag)) {
            $missingFields[] = 'w_jute_bag';
        }

        if (is_null($bill->w_dunnage_dribag)) {
            $missingFields[] = 'w_dunnage_dribag';
        }

        return $missingFields;
    }
}
