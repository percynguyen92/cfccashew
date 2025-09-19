<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\Container;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ContainerQuery
{
    public function __construct(
        private Container $model
    ) {}

    public function getByBillId(int $billId): Collection
    {
        return $this->model
            ->where('bill_id', $billId)
            ->with(['cuttingTests' => function ($query) {
                $query->orderBy('created_at', 'desc');
            }])
            ->orderBy('created_at')
            ->get();
    }

    public function getContainersWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->whereHas('cuttingTests', function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            })
            ->with([
                'bill:id,bill_number,seller,buyer',
                'cuttingTests' => function ($query) use ($threshold) {
                    $query->where('moisture', '>', $threshold)
                          ->orderBy('moisture', 'desc');
                }
            ])
            ->get();
    }

    public function getContainersPendingCuttingTests(): Collection
    {
        return $this->model
            ->whereDoesntHave('cuttingTests')
            ->with('bill:id,bill_number,seller,buyer')
            ->orderBy('created_at')
            ->get();
    }

    public function search(array $filters = []): Builder
    {
        $query = $this->model->query()->with('bill');

        if (!empty($filters['bill_id'])) {
            $query->where('bill_id', $filters['bill_id']);
        }

        if (!empty($filters['container_number'])) {
            $query->where('container_number', 'like', "%{$filters['container_number']}%");
        }

        if (!empty($filters['truck'])) {
            $query->where('truck', 'like', "%{$filters['truck']}%");
        }

        return $query;
    }
}