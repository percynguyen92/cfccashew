<?php

declare(strict_types=1);

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreBillRequest;
use App\Http\Requests\V1\UpdateBillRequest;
use App\Http\Resources\V1\BillResource;
use App\Models\Bill;
use Illuminate\Http\Response;

class BillController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return BillResource::collection(Bill::with('containers')->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBillRequest $request): BillResource
    {
        $bill = Bill::create($request->validated());

        return new BillResource($bill);
    }

    /**
     * Display the specified resource.
     */
    public function show(Bill $bill): BillResource
    {
        return new BillResource($bill->load('containers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBillRequest $request, Bill $bill): BillResource
    {
        $bill->update($request->validated());

        return new BillResource($bill);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bill $bill): Response
    {
        $bill->delete();

        return response()->noContent();
    }
}