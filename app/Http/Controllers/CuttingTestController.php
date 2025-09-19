<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreCuttingTestRequest;
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
        $billId = $request->get('bill_id');
        $cuttingTests = $billId 
            ? $this->cuttingTestService->getCuttingTestsByBillId((int) $billId)
            : collect();

        return Inertia::render('CuttingTests/Index', [
            'cutting_tests' => CuttingTestResource::collection($cuttingTests),
            'bill_id' => $billId,
        ]);
    }

    /**
     * Show the form for creating a new cutting test.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('CuttingTests/Create', [
            'bill_id' => $request->get('bill_id'),
            'container_id' => $request->get('container_id'),
        ]);
    }

    /**
     * Store a newly created cutting test in storage.
     */
    public function store(StoreCuttingTestRequest $request): RedirectResponse
    {
        $cuttingTest = $this->cuttingTestService->createCuttingTest($request->validated());

        return redirect()->route('bills.show', $cuttingTest->bill_id)
            ->with('success', 'Cutting test created successfully.');
    }

    /**
     * Display the specified cutting test.
     */
    public function show(CuttingTest $cuttingTest): Response
    {
        $cuttingTestData = $this->cuttingTestService->getCuttingTestById($cuttingTest->id);

        return Inertia::render('CuttingTests/Show', [
            'cutting_test' => new CuttingTestResource($cuttingTestData),
        ]);
    }

    /**
     * Show the form for editing the specified cutting test.
     */
    public function edit(CuttingTest $cuttingTest): Response
    {
        return Inertia::render('CuttingTests/Edit', [
            'cutting_test' => new CuttingTestResource($cuttingTest),
        ]);
    }

    /**
     * Update the specified cutting test in storage.
     */
    public function update(StoreCuttingTestRequest $request, CuttingTest $cuttingTest): RedirectResponse
    {
        $this->cuttingTestService->updateCuttingTest($cuttingTest, $request->validated());

        return redirect()->route('bills.show', $cuttingTest->bill_id)
            ->with('success', 'Cutting test updated successfully.');
    }

    /**
     * Remove the specified cutting test from storage.
     */
    public function destroy(CuttingTest $cuttingTest): RedirectResponse
    {
        $billId = $cuttingTest->bill_id;
        $this->cuttingTestService->deleteCuttingTest($cuttingTest);

        return redirect()->route('bills.show', $billId)
            ->with('success', 'Cutting test deleted successfully.');
    }
}