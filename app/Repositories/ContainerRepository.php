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
            ->where('bill_id', $billId)
            ->with('cuttingTests')
            ->get();
    }

    public function getContainersWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->model
            ->whereHas('cuttingTests', function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            })
            ->with(['bill', 'cuttingTests' => function ($query) use ($threshold) {
                $query->where('moisture', '>', $threshold);
            }])
            ->get();
    }

    public function getContainersPendingCuttingTests(): Collection
    {
        return $this->model
            ->whereDoesntHave('cuttingTests')
            ->with('bill')
            ->get();
    }
}