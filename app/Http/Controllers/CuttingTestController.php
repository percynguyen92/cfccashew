<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuttingTestRequest;
use App\Http\Requests\UpdateCuttingTestRequest;
use App\Http\Resources\CuttingTestResource;
use App\Models\CuttingTest;
use App\Services\CuttingTestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CuttingTestController extends Controller
{
    public function __construct(
        private CuttingTestService $cuttingTestService
    ) {}

    /**
     * Display a listing of cutting tests.
     */
    public function index(Request $request): Response
    {
        $filters = $request->only([
            'bill_number',
            'test_type', 
            'container_id',
            'moisture_min',
            'moisture_max',
            'date_from',
            'date_to',
            'per_page',
            'page'
        ]);

        $cuttingTests = $this->cuttingTestService->getCuttingTestsWithFilters($filters);

        return Inertia::render('CuttingTests/Index', [
            'cutting_tests' => CuttingTestResource::collection($cuttingTests->items())->resolve(),
            'pagination' => [
                'current_page' => $cuttingTests->currentPage(),
                'last_page' => $cuttingTests->lastPage(),
                'per_page' => $cuttingTests->perPage(),
                'total' => $cuttingTests->total(),
                'from' => $cuttingTests->firstItem(),
                'to' => $cuttingTests->lastItem(),
            ],
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new cutting test.
     */
    public function create(Request $request): Response
    {
        $billId = $request->get('bill_id');
        $containerId = $request->get('container_id');
        
        // Get bill and container data for form context
        $bill = $billId ? $this->cuttingTestService->getBillById((int) $billId) : null;
        $container = $containerId ? $this->cuttingTestService->getContainerById((int) $containerId) : null;
        
        return Inertia::render('CuttingTests/Create', [
            'bill_id' => $billId,
            'container_id' => $containerId,
            'bill' => $bill ? new \App\Http\Resources\BillResource($bill) : null,
            'container' => $container ? new \App\Http\Resources\ContainerResource($container) : null,
        ]);
    }

    /**
     * Store a newly created cutting test in storage.
     */
    public function store(StoreCuttingTestRequest $request): RedirectResponse
    {
        $cuttingTest = $this->cuttingTestService->createCuttingTest($request->validated());

        return redirect()->route('bills.show', $cuttingTest->bill_id)
            ->with('success', __('messages.cutting_test_created'));
    }

    /**
     * Display the specified cutting test.
     */
    public function show(CuttingTest $cuttingTest): Response
    {
        $cuttingTestData = $this->cuttingTestService->getCuttingTestById($cuttingTest->id);

        return Inertia::render('CuttingTests/Show', [
            'cutting_test' => (new CuttingTestResource($cuttingTestData))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified cutting test.
     */
    public function edit(CuttingTest $cuttingTest): Response
    {
        return Inertia::render('CuttingTests/Edit', [
            'cutting_test' => (new CuttingTestResource($cuttingTest))->resolve(),
        ]);
    }

    /**
     * Update the specified cutting test in storage.
     */
    public function update(UpdateCuttingTestRequest $request, CuttingTest $cuttingTest): RedirectResponse
    {
        $this->cuttingTestService->updateCuttingTest($cuttingTest, $request->validated());

        return redirect()->route('bills.show', $cuttingTest->bill_id)
            ->with('success', __('messages.cutting_test_updated'));
    }

    /**
     * Remove the specified cutting test from storage.
     */
    public function destroy(CuttingTest $cuttingTest): RedirectResponse
    {
        $billId = $cuttingTest->bill_id;
        $this->cuttingTestService->deleteCuttingTest($cuttingTest);

        return redirect()->route('bills.show', $billId)
            ->with('success', __('messages.cutting_test_deleted'));
    }
}
