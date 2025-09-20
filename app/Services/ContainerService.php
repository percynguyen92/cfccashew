<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Container;
use App\Repositories\ContainerRepository;
use App\Queries\ContainerQuery;
use Illuminate\Database\Eloquent\Collection;

class ContainerService
{
    public function __construct(
        private ContainerRepository $containerRepository,
        private ContainerQuery $containerQuery
    ) {}

    public function getContainerById(int $id): ?Container
    {
        return $this->containerRepository->findByIdWithRelations($id, ['cuttingTests', 'bill']);
    }

    public function getContainerByIdentifier(string $identifier): ?Container
    {
        return $this->containerRepository->findByContainerNumberOrId($identifier, ['cuttingTests', 'bill']);
    }

    public function getContainersByBillId(int $billId): Collection
    {
        return $this->containerQuery->getByBillId($billId);
    }

    public function createContainer(array $data): Container
    {
        // Calculate derived weights if needed
        $data = $this->calculateWeights($data);
        
        return $this->containerRepository->create($data);
    }

    public function updateContainer(Container $container, array $data): bool
    {
        // Recalculate derived weights if needed
        $data = $this->calculateWeights($data);
        
        return $this->containerRepository->update($container, $data);
    }

    public function deleteContainer(Container $container): bool
    {
        return $this->containerRepository->delete($container);
    }

    public function getContainersWithHighMoisture(float $threshold = 11.0): Collection
    {
        return $this->containerQuery->getContainersWithHighMoisture($threshold);
    }

    public function getContainersPendingCuttingTests(): Collection
    {
        return $this->containerQuery->getContainersPendingCuttingTests();
    }

    public function calculateAverageMoisture(Container $container): ?float
    {
        $tests = $container->cuttingTests()->whereNotNull('moisture')->get();
        
        if ($tests->isEmpty()) {
            return null;
        }

        return round($tests->avg('moisture'), 1);
    }

    public function getOutturnRate(Container $container): ?float
    {
        $test = $container->cuttingTests()->whereNotNull('outturn_rate')->first();
        
        return $test ? $test->outturn_rate : null;
    }

    private function calculateWeights(array $data): array
    {
        // Calculate w_tare if we have the necessary components
        if (isset($data['w_truck'], $data['w_container'], $data['w_dunnage_dribag'])) {
            $data['w_tare'] = ($data['w_truck'] + $data['w_container'] + $data['w_dunnage_dribag']);
        }

        // Calculate w_net if we have gross and tare
        if (isset($data['w_gross'], $data['w_tare'])) {
            $data['w_net'] = $data['w_gross'] - $data['w_tare'];
        }

        return $data;
    }

    public function getAllContainersPaginated(array $filters = [], int $perPage = 15)
    {
        return $this->containerQuery->getAllPaginated($filters, $perPage);
    }

    public function getContainerStatistics(): array
    {
        $highMoistureContainers = $this->getContainersWithHighMoisture();
        $pendingTests = $this->getContainersPendingCuttingTests();

        return [
            'high_moisture_count' => $highMoistureContainers->count(),
            'pending_tests_count' => $pendingTests->count(),
            'high_moisture_containers' => $highMoistureContainers,
            'pending_tests_containers' => $pendingTests,
        ];
    }
}