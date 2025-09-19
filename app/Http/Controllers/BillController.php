<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\StoreBillRequest;
use App\Http\Requests\UpdateBillRequest;
use App\Http\Resources\BillResource;
use App\Models\Bill;
use App\Services\BillService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BillController extends Controller
{
    public function __construct(
        private BillService $billService
    ) {}

    /**
     * Display a listing of bills.
     */
    public function index(Request $request): Response
    {
        $filters = [
            'search' => $request->get('search', ''),
            'sort_by' => $request->get('sort_by', 'created_at'),
            'sort_direction' => $request->get('sort_direction', 'desc'),
        ];

        $bills = $this->billService->getAllBills($filters, 15);

        return Inertia::render('Bills/Index', [
            'bills' => $bills,
            'filters' => $filters,
        ]);
    }

    /**
     * Show the form for creating a new bill.
     */
    public function create(): Response
    {
        return Inertia::render('Bills/Create');
    }

    /**
     * Store a newly created bill in storage.
     */
    public function store(StoreBillRequest $request): RedirectResponse
    {
        $bill = $this->billService->createBill($request->validated());

        return redirect()->route('bills.show', $bill->id)
            ->with('success', 'Bill created successfully.');
    }

    /**
     * Display the specified bill.
     */
    public function show(Bill $bill): Response
    {
        $billData = $this->billService->getBillById($bill->id);

        return Inertia::render('Bills/Show', [
            'bill' => (new BillResource($billData))->resolve(),
        ]);
    }

    /**
     * Show the form for editing the specified bill.
     */
    public function edit(Bill $bill): Response
    {
        return Inertia::render('Bills/Edit', [
            'bill' => new BillResource($bill),
        ]);
    }

    /**
     * Update the specified bill in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill): RedirectResponse
    {
        $this->billService->updateBill($bill, $request->validated());

        return redirect()->route('bills.show', $bill->id)
            ->with('success', 'Bill updated successfully.');
    }

    /**
     * Remove the specified bill from storage.
     */
    public function destroy(Bill $bill): RedirectResponse
    {
        $this->billService->deleteBill($bill);

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully.');
    }
}