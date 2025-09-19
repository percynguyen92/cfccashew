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
}