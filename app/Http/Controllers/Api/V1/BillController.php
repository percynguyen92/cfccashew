<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BillQuery;
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
    public function index(BillQuery $request)
    {
        $query = $request->apply(Bill::query());

        return BillResource::collection($query->paginate(perPage: 10));
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
        // Allow including relationships on the show route as well
        if (request()->has('include')) {
            $bill->load(array_intersect(['containers', 'cuttingTests'], explode(',', request()->input('include', ''))));
        }

        return new BillResource($bill);
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