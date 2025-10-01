<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Bill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BillRepository
{
    public function __construct(
        private Bill $model
    ) {}

    public function findById(int $id): ?Bill
    {
        return $this->model->find($id);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?Bill
    {
        return $this->model->with($relations)->find($id);
    }

    public function create(array $data): Bill
    {
        return $this->model->create($data);
    }

    public function update(Bill $bill, array $data): bool
    {
        return $bill->update($data);
    }

    public function delete(Bill $bill): bool
    {
        return $bill->delete();
    }

    public function getAll(): Collection
    {
        return $this->model->all();
    }

    public function getAllWithCounts(): Collection
    {
        return $this->model
            ->withCount(['containers', 'finalSamples'])
            ->get();
    }

    public function getRecentBills(int $limit = 10): Collection
    {
        return $this->model
            ->withCount(['containers', 'finalSamples'])
            ->orderBy('updated_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getBillsPendingFinalTests(): Collection
    {
        return $this->model
            ->whereDoesntHave('finalSamples', function ($query) {
                $query->whereIn('type', [1, 2, 3]);
            })
            ->withCount('containers')
            ->get();
    }

    public function getBillsMissingFinalSamples(): Collection
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

    /**
     * Find bills by inspection date range
     */
    public function findByInspectionDateRange(?\DateTime $startDate = null, ?\DateTime $endDate = null): Collection
    {
        $query = $this->model->query();

        if ($startDate) {
            $query->where('inspection_start_date', '>=', $startDate->format('Y-m-d'));
        }

        if ($endDate) {
            $query->where('inspection_end_date', '<=', $endDate->format('Y-m-d'));
        }

        return $query->withCount(['containers', 'finalSamples'])->get();
    }

    /**
     * Filter bills by origin
     */
    public function findByOrigin(string $origin): Collection
    {
        return $this->model
            ->where('origin', 'like', "%{$origin}%")
            ->withCount(['containers', 'finalSamples'])
            ->get();
    }

    /**
     * Filter bills by sampling ratio range
     */
    public function findBySamplingRatioRange(float $minRatio, float $maxRatio): Collection
    {
        return $this->model
            ->whereBetween('sampling_ratio', [$minRatio, $maxRatio])
            ->withCount(['containers', 'finalSamples'])
            ->get();
    }

    /**
     * Get bills with all related data efficiently loaded
     */
    public function findWithCompleteRelations(int $id): ?Bill
    {
        return $this->model
            ->with([
                'containers' => function ($query) {
                    $query->with('cuttingTests');
                },
                'finalSamples',
                'cuttingTests'
            ])
            ->find($id);
    }

    /**
     * Get paginated bills with filters and optimized queries
     */
    public function findWithFilters(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        // Filter by bill number
        if (!empty($filters['bill_number'])) {
            $query->where('bill_number', 'like', "%{$filters['bill_number']}%");
        }

        // Filter by seller
        if (!empty($filters['seller'])) {
            $query->where('seller', 'like', "%{$filters['seller']}%");
        }

        // Filter by buyer
        if (!empty($filters['buyer'])) {
            $query->where('buyer', 'like', "%{$filters['buyer']}%");
        }

        // Filter by origin
        if (!empty($filters['origin'])) {
            $query->where('origin', 'like', "%{$filters['origin']}%");
        }

        // Filter by inspection location
        if (!empty($filters['inspection_location'])) {
            $query->where('inspection_location', 'like', "%{$filters['inspection_location']}%");
        }

        // Filter by inspection date range
        if (!empty($filters['inspection_start_from'])) {
            $query->where('inspection_start_date', '>=', $filters['inspection_start_from']);
        }

        if (!empty($filters['inspection_start_to'])) {
            $query->where('inspection_start_date', '<=', $filters['inspection_start_to']);
        }

        if (!empty($filters['inspection_end_from'])) {
            $query->where('inspection_end_date', '>=', $filters['inspection_end_from']);
        }

        if (!empty($filters['inspection_end_to'])) {
            $query->where('inspection_end_date', '<=', $filters['inspection_end_to']);
        }

        // Filter by sampling ratio range
        if (!empty($filters['sampling_ratio_min'])) {
            $query->where('sampling_ratio', '>=', $filters['sampling_ratio_min']);
        }

        if (!empty($filters['sampling_ratio_max'])) {
            $query->where('sampling_ratio', '<=', $filters['sampling_ratio_max']);
        }

        // Filter by net weight on BL range
        if (!empty($filters['net_on_bl_min'])) {
            $query->where('net_on_bl', '>=', $filters['net_on_bl_min']);
        }

        if (!empty($filters['net_on_bl_max'])) {
            $query->where('net_on_bl', '<=', $filters['net_on_bl_max']);
        }

        // Filter by quantity of bags on BL range
        if (!empty($filters['quantity_of_bags_on_bl_min'])) {
            $query->where('quantity_of_bags_on_bl', '>=', $filters['quantity_of_bags_on_bl_min']);
        }

        if (!empty($filters['quantity_of_bags_on_bl_max'])) {
            $query->where('quantity_of_bags_on_bl', '<=', $filters['quantity_of_bags_on_bl_max']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query
            ->withCount(['containers', 'finalSamples'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get bills with containers that have high moisture content
     */
    public function getBillsWithHighMoistureContainers(float $threshold = 11.0): Collection
    {
        return $this->model
            ->whereHas('containers.cuttingTests', function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            })
            ->with([
                'containers' => function ($query) use ($threshold) {
                    $query->whereHas('cuttingTests', function ($subQuery) use ($threshold) {
                        $subQuery->where('moisture', '>', $threshold);
                    })->with(['cuttingTests' => function ($testQuery) use ($threshold) {
                        $testQuery->where('moisture', '>', $threshold);
                    }]);
                }
            ])
            ->get();
    }
}