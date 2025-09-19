<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BillResource;
use App\Http\Resources\ContainerResource;
use App\Http\Resources\CuttingTestResource;
use App\Services\BillService;
use App\Services\ContainerService;
use App\Services\CuttingTestService;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function __construct(
        private BillService $billService,
        private ContainerService $containerService,
        private CuttingTestService $cuttingTestService
    ) {}

    /**
     * Get dashboard statistics and KPIs.
     */
    public function stats(): JsonResponse
    {
        $billStats = $this->billService->getBillStatistics();
        $containerStats = $this->containerService->getContainerStatistics();
        $cuttingTestStats = $this->cuttingTestService->getCuttingTestStatistics();

        return response()->json([
            'bills' => [
                'total' => $billStats['total_bills'],
                'pending_final_tests' => $billStats['pending_final_tests_count'],
                'missing_final_samples' => $billStats['missing_final_samples_count'],
            ],
            'containers' => [
                'high_moisture' => $containerStats['high_moisture_count'],
                'pending_tests' => $containerStats['pending_tests_count'],
            ],
            'cutting_tests' => [
                'high_moisture' => $cuttingTestStats['high_moisture_count'],
                'moisture_distribution' => $cuttingTestStats['moisture_distribution'],
            ],
        ]);
    }

    /**
     * Get recent bills for dashboard widget.
     */
    public function recentBills(): JsonResponse
    {
        $recentBills = $this->billService->getRecentBills(5);

        return response()->json([
            'data' => BillResource::collection($recentBills),
        ]);
    }

    /**
     * Get bills pending final tests.
     */
    public function billsPendingFinalTests(): JsonResponse
    {
        $bills = $this->billService->getBillsPendingFinalTests();

        return response()->json([
            'data' => BillResource::collection($bills),
        ]);
    }

    /**
     * Get bills missing final samples.
     */
    public function billsMissingFinalSamples(): JsonResponse
    {
        $bills = $this->billService->getBillsMissingFinalSamples();

        return response()->json([
            'data' => BillResource::collection($bills),
        ]);
    }

    /**
     * Get containers with high moisture.
     */
    public function containersHighMoisture(): JsonResponse
    {
        $containers = $this->containerService->getContainersWithHighMoisture();

        return response()->json([
            'data' => ContainerResource::collection($containers),
        ]);
    }

    /**
     * Get cutting tests with high moisture.
     */
    public function cuttingTestsHighMoisture(): JsonResponse
    {
        $tests = $this->cuttingTestService->getTestsWithHighMoisture();

        return response()->json([
            'data' => CuttingTestResource::collection($tests),
        ]);
    }

    /**
     * Get moisture distribution statistics.
     */
    public function moistureDistribution(): JsonResponse
    {
        $distribution = $this->cuttingTestService->getMoistureDistribution();

        return response()->json($distribution);
    }
}