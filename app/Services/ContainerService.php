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
        return $this->containerRepository->findByIdWithRelations($id, ['cuttingTests', 'bills']);
    }

    public function getContainerByIdentifier(string $identifier): ?Container
    {
        return $this->containerRepository->findByContainerNumberOrId($identifier, ['cuttingTests', 'bills']);
    }

    public function getContainersByBillId(int $billId): Collection
    {
        return $this->containerQuery->getByBillId($billId);
    }

    public function createContainer(array $data): Container
    {
        // Validate container number format
        $this->validateContainerNumber($data);
        
        // Calculate derived weights if needed
        $data = $this->calculateWeights($data);
        
        $billId = $data['bill_id'] ?? null;
        unset($data['bill_id']);
        
        $container = $this->containerRepository->create($data);
        
        if ($billId) {
            $container->bills()->attach($billId);
            // Recalculate weights with Bill data
            $this->updateCalculatedWeights($container);
        }
        
        return $container->load('bills');
    }

    public function updateContainer(Container $container, array $data): bool
    {
        // Validate container number format if provided
        $this->validateContainerNumber($data);
        
        // Handle bill association
        if (isset($data['bill_id'])) {
            $container->bills()->sync([$data['bill_id']]);
            unset($data['bill_id']);
        }
        
        // Recalculate derived weights if needed
        $data = $this->calculateWeights($data);
        
        $updated = $this->containerRepository->update($container, $data);
        
        if ($updated) {
            // Recalculate weights with Bill data
            $this->updateCalculatedWeights($container);
        }
        
        return $updated;
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
        
        return $test ? (float) $test->outturn_rate : null;
    }

    private function calculateWeights(array $data): array
    {
        // Calculate w_gross if we have the necessary components: w_gross = w_total - w_truck - w_container
        if (isset($data['w_total'], $data['w_truck'], $data['w_container'])) {
            $data['w_gross'] = max(0, $data['w_total'] - $data['w_truck'] - $data['w_container']);
        }
        
        // For w_tare and w_net calculations, we need to get Bill data
        // These will be calculated after container creation/update when Bill relationship is established
        
        return $data;
    }

    /**
     * Calculate weights using Bill model data for w_dunnage_dribag and w_jute_bag
     */
    public function calculateContainerWeights(Container $container): array
    {
        $calculations = [];
        
        // Get the associated bill
        $bill = $container->bills()->first();
        if (!$bill) {
            return ['error' => 'No associated bill found for weight calculations'];
        }

        // Calculate w_gross: w_gross = w_total - w_truck - w_container
        if (!is_null($container->w_total) && !is_null($container->w_truck) && !is_null($container->w_container)) {
            $calculations['w_gross'] = max(0, $container->w_total - $container->w_truck - $container->w_container);
        }

        // Calculate w_tare: w_tare = quantity_of_bags * w_jute_bag (from Bill)
        if (!is_null($container->quantity_of_bags) && !is_null($bill->w_jute_bag)) {
            $calculations['w_tare'] = $container->quantity_of_bags * $bill->w_jute_bag;
        }

        // Calculate w_net: w_net = w_gross - w_dunnage_dribag - w_tare (using Bill's w_dunnage_dribag)
        if (isset($calculations['w_gross']) && isset($calculations['w_tare']) && !is_null($bill->w_dunnage_dribag)) {
            $calculations['w_net'] = max(0, $calculations['w_gross'] - $bill->w_dunnage_dribag - $calculations['w_tare']);
        }

        return $calculations;
    }

    /**
     * Update container with calculated weights
     */
    public function updateCalculatedWeights(Container $container): bool
    {
        $calculations = $this->calculateContainerWeights($container);
        
        if (isset($calculations['error'])) {
            return false;
        }

        // Update the container with calculated values
        foreach ($calculations as $field => $value) {
            $container->{$field} = $value;
        }

        return $container->save();
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



    /**
     * Validate container number format (4 letters + 7 digits)
     */
    private function validateContainerNumber(array $data): void
    {
        if (isset($data['container_number']) && !empty($data['container_number'])) {
            if (!preg_match('/^[A-Z]{4}\d{7}$/', $data['container_number'])) {
                throw new \InvalidArgumentException('Container number must follow ISO format: 4 letters followed by 7 digits (e.g., ABCD1234567)');
            }
        }
    }

    /**
     * Get weight discrepancy validation alerts for a container
     */
    public function getWeightDiscrepancyAlerts(Container $container): array
    {
        $alerts = [];
        $bill = $container->bills()->first();
        
        if (!$bill) {
            $alerts[] = 'No associated bill found for weight validation';
            return $alerts;
        }

        // Check if calculated weights differ significantly from stored weights
        $calculations = $this->calculateContainerWeights($container);
        
        if (isset($calculations['w_gross']) && !is_null($container->w_gross)) {
            $grossDifference = abs($calculations['w_gross'] - $container->w_gross);
            if ($grossDifference > 10) { // 10kg tolerance
                $alerts[] = "Gross weight discrepancy: calculated {$calculations['w_gross']}kg vs stored {$container->w_gross}kg";
            }
        }

        if (isset($calculations['w_tare']) && !is_null($container->w_tare)) {
            $tareDifference = abs($calculations['w_tare'] - $container->w_tare);
            if ($tareDifference > 5) { // 5kg tolerance
                $alerts[] = "Tare weight discrepancy: calculated {$calculations['w_tare']}kg vs stored {$container->w_tare}kg";
            }
        }

        if (isset($calculations['w_net']) && !is_null($container->w_net)) {
            $netDifference = abs($calculations['w_net'] - $container->w_net);
            if ($netDifference > 10) { // 10kg tolerance
                $alerts[] = "Net weight discrepancy: calculated {$calculations['w_net']}kg vs stored {$container->w_net}kg";
            }
        }

        // Check for missing weight data
        if (is_null($container->w_total)) {
            $alerts[] = 'Total weight is missing';
        }

        if (is_null($container->w_truck)) {
            $alerts[] = 'Truck weight is missing';
        }

        if (is_null($container->w_container)) {
            $alerts[] = 'Container weight is missing';
        }

        // Check for logical weight relationships
        if (!is_null($container->w_gross) && !is_null($container->w_net) && $container->w_net > $container->w_gross) {
            $alerts[] = 'Net weight cannot be greater than gross weight';
        }

        return $alerts;
    }

    /**
     * Get containers with weight discrepancies
     */
    public function getContainersWithWeightDiscrepancies(): Collection
    {
        $containers = $this->containerRepository->getAll();
        $containersWithDiscrepancies = collect();

        foreach ($containers as $container) {
            $alerts = $this->getWeightDiscrepancyAlerts($container);
            if (!empty($alerts)) {
                $container->weight_alerts = $alerts;
                $containersWithDiscrepancies->push($container);
            }
        }

        return $containersWithDiscrepancies;
    }

    /**
     * Get containers by condition (for logging purposes)
     */
    public function getContainersByCondition(string $condition): Collection
    {
        return $this->containerRepository->getAll()
            ->where('container_condition', $condition)
            ->orWhere('seal_condition', $condition);
    }
}
