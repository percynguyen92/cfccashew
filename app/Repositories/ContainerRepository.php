<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Container;
use Illuminate\Database\Eloquent\Collection;

class ContainerRepository
{
    public function __construct(
        private Container $model
    ) {}

    public function findById(int $id): ?Container
    {
        return $this->model->find($id);
    }

    public function findByIdWithRelations(int $id, array $relations = []): ?Container
    {
        return $this->model->with($relations)->find($id);
    }

    public function findByContainerNumber(string $containerNumber, array $relations = []): ?Container
    {
        return $this->model->with($relations)->where('container_number', $containerNumber)->first();
    }

    public function findByContainerNumberOrId(string $identifier, array $relations = []): ?Container
    {
        // If the identifier looks like a container number (4 letters + 7 digits), search by container_number
        if (preg_match('/^[A-Z]{4}\d{7}$/', $identifier)) {
            return $this->findByContainerNumber($identifier, $relations);
        }
        
        // Otherwise, treat it as an ID
        if (is_numeric($identifier)) {
            return $this->findByIdWithRelations((int) $identifier, $relations);
        }
        
        return null;
    }

    public function create(array $data): Container
    {
        return $this->model->create($data);
    }

    public function update(Container $container, array $data): bool
    {
        return $container->update($data);
    }

    public function delete(Container $container): bool
    {
        return $container->delete();
    }

    public function getByBillId(int $billId): Collection
    {
        return $this->model
            ->whereHas('bills', function ($query) use ($billId) {
                $query->where('bills.id', $billId);
            })
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    public function getContainersWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->whereHas('cuttingTests', function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            })
            ->with(['bills', 'cuttingTests' => function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            }])
            ->get();
    }

    public function getContainersPendingCuttingTests(): Collection
    {
        return $this->model
            ->whereDoesntHave('cuttingTests')
            ->with('bills')
            ->get();
    }

    /**
     * Filter containers by container condition
     */
    public function findByContainerCondition(string $condition): Collection
    {
        return $this->model
            ->where('container_condition', $condition)
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Filter containers by seal condition
     */
    public function findBySealCondition(string $condition): Collection
    {
        return $this->model
            ->where('seal_condition', $condition)
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Filter containers by both container and seal conditions
     */
    public function findByConditions(string $containerCondition, string $sealCondition): Collection
    {
        return $this->model
            ->where('container_condition', $containerCondition)
            ->where('seal_condition', $sealCondition)
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Find containers within a weight range (total weight)
     */
    public function findByTotalWeightRange(float $minWeight, float $maxWeight): Collection
    {
        return $this->model
            ->whereBetween('w_total', [$minWeight, $maxWeight])
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Find containers within a gross weight range
     */
    public function findByGrossWeightRange(float $minWeight, float $maxWeight): Collection
    {
        return $this->model
            ->whereBetween('w_gross', [$minWeight, $maxWeight])
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Find containers within a net weight range
     */
    public function findByNetWeightRange(float $minWeight, float $maxWeight): Collection
    {
        return $this->model
            ->whereBetween('w_net', [$minWeight, $maxWeight])
            ->with(['bills', 'cuttingTests'])
            ->get();
    }

    /**
     * Get containers with moisture alerts (>11% by default)
     */
    public function getContainersWithMoistureAlerts(float $threshold = 11.0): Collection
    {
        return $this->model
            ->whereHas('cuttingTests', function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            })
            ->with([
                'bills',
                'cuttingTests' => function ($query) use ($threshold) {
                    $query->where('moisture', '>', $threshold)
                          ->orderBy('moisture', 'desc');
                }
            ])
            ->get();
    }

    /**
     * Get containers with comprehensive filters
     */
    public function findWithFilters(array $filters = []): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->model->query();

        // Filter by container number
        if (!empty($filters['container_number'])) {
            $query->where('container_number', 'like', "%{$filters['container_number']}%");
        }

        // Filter by truck
        if (!empty($filters['truck'])) {
            $query->where('truck', 'like', "%{$filters['truck']}%");
        }

        // Filter by container condition
        if (!empty($filters['container_condition'])) {
            $query->where('container_condition', $filters['container_condition']);
        }

        // Filter by seal condition
        if (!empty($filters['seal_condition'])) {
            $query->where('seal_condition', $filters['seal_condition']);
        }

        // Filter by quantity of bags range
        if (!empty($filters['quantity_of_bags_min'])) {
            $query->where('quantity_of_bags', '>=', $filters['quantity_of_bags_min']);
        }

        if (!empty($filters['quantity_of_bags_max'])) {
            $query->where('quantity_of_bags', '<=', $filters['quantity_of_bags_max']);
        }

        // Filter by total weight range
        if (!empty($filters['w_total_min'])) {
            $query->where('w_total', '>=', $filters['w_total_min']);
        }

        if (!empty($filters['w_total_max'])) {
            $query->where('w_total', '<=', $filters['w_total_max']);
        }

        // Filter by gross weight range
        if (!empty($filters['w_gross_min'])) {
            $query->where('w_gross', '>=', $filters['w_gross_min']);
        }

        if (!empty($filters['w_gross_max'])) {
            $query->where('w_gross', '<=', $filters['w_gross_max']);
        }

        // Filter by net weight range
        if (!empty($filters['w_net_min'])) {
            $query->where('w_net', '>=', $filters['w_net_min']);
        }

        if (!empty($filters['w_net_max'])) {
            $query->where('w_net', '<=', $filters['w_net_max']);
        }

        // Filter by truck weight range
        if (!empty($filters['w_truck_min'])) {
            $query->where('w_truck', '>=', $filters['w_truck_min']);
        }

        if (!empty($filters['w_truck_max'])) {
            $query->where('w_truck', '<=', $filters['w_truck_max']);
        }

        // Filter by container weight range
        if (!empty($filters['w_container_min'])) {
            $query->where('w_container', '>=', $filters['w_container_min']);
        }

        if (!empty($filters['w_container_max'])) {
            $query->where('w_container', '<=', $filters['w_container_max']);
        }

        // Filter by tare weight range
        if (!empty($filters['w_tare_min'])) {
            $query->where('w_tare', '>=', $filters['w_tare_min']);
        }

        if (!empty($filters['w_tare_max'])) {
            $query->where('w_tare', '<=', $filters['w_tare_max']);
        }

        // Filter by bill ID
        if (!empty($filters['bill_id'])) {
            $query->whereHas('bills', function ($billQuery) use ($filters) {
                $billQuery->where('bills.id', $filters['bill_id']);
            });
        }

        // Filter by bill number
        if (!empty($filters['bill_number'])) {
            $query->whereHas('bills', function ($billQuery) use ($filters) {
                $billQuery->where('bill_number', 'like', "%{$filters['bill_number']}%");
            });
        }

        // Filter containers with high moisture
        if (!empty($filters['high_moisture']) && $filters['high_moisture']) {
            $threshold = $filters['moisture_threshold'] ?? 11.0;
            $query->whereHas('cuttingTests', function ($testQuery) use ($threshold) {
                $testQuery->where('moisture', '>', $threshold);
            });
        }

        // Filter by creation date range
        if (!empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (!empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        $perPage = (int) ($filters['per_page'] ?? 15);

        return $query
            ->with(['bills', 'cuttingTests'])
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get containers with weight discrepancies based on business rules
     */
    public function getContainersWithWeightDiscrepancies(): Collection
    {
        return $this->model
            ->whereNotNull('w_total')
            ->whereNotNull('w_truck')
            ->whereNotNull('w_container')
            ->whereNotNull('w_gross')
            ->whereNotNull('w_net')
            ->with(['bills', 'cuttingTests'])
            ->get()
            ->filter(function ($container) {
                // Calculate expected gross weight
                $expectedGross = $container->w_total - $container->w_truck - $container->w_container;
                
                // Check if there's a significant discrepancy (more than 5kg difference)
                return abs($container->w_gross - $expectedGross) > 5;
            });
    }

    /**
     * Get containers by condition status (both container and seal)
     */
    public function getContainersByConditionStatus(array $containerConditions = [], array $sealConditions = []): Collection
    {
        $query = $this->model->query();

        if (!empty($containerConditions)) {
            $query->whereIn('container_condition', $containerConditions);
        }

        if (!empty($sealConditions)) {
            $query->whereIn('seal_condition', $sealConditions);
        }

        return $query
            ->with(['bills', 'cuttingTests'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }
}