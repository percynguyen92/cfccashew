<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bill;
use App\Repositories\BillRepository;
use App\Queries\BillQuery;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

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
        return $this->billRepository->create($data);
    }

    public function updateBill(Bill $bill, array $data): bool
    {
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
}
