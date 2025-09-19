<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class BillQuery
{
    public function __construct(
        private Bill $model
    ) {}

    public function search(array $filters = []): Builder
    {
        $query = $this->model->query()
            ->withCount(['containers', 'finalSamples'])
            ->with(['finalSamples' => function ($query) {
                $query->whereNotNull('outturn_rate');
            }]);

        // Apply search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('bill_number', 'like', "%{$search}%")
                  ->orWhere('seller', 'like', "%{$search}%")
                  ->orWhere('buyer', 'like', "%{$search}%");
            });
        }

        // Apply sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';
        
        $allowedSorts = ['bill_number', 'seller', 'buyer', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        return $query;
    }

    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $bills = $this->search($filters)->paginate($perPage)->withQueryString();

        // Calculate average outurn for each bill
        $bills->getCollection()->transform(function ($bill) {
            $finalSamples = $bill->finalSamples->whereNotNull('outturn_rate');
            $bill->average_outurn = $finalSamples->isNotEmpty() 
                ? round($finalSamples->avg('outturn_rate'), 2) 
                : null;
            return $bill;
        });

        return $bills;
    }

    public function getRecentBills(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->withCount(['containers', 'finalSamples'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getBillsPendingFinalTests(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->whereDoesntHave('finalSamples', function ($query) {
                $query->whereIn('type', [1, 2, 3]);
            })
            ->withCount('containers')
            ->get();
    }

    public function getBillsMissingFinalSamples(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model
            ->whereHas('containers')
            ->where(function ($query) {
                for ($type = 1; $type <= 3; $type++) {
                    $query->orWhereDoesntHave('finalSamples', function ($subQuery) use ($type) {
                        $subQuery->where('type', $type);
                    });
                }
            })
            ->withCount('containers')
            ->get();
    }
}