<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'bill_id' => $this->bill_id,
            'truck' => $this->truck,
            'container_number' => $this->container_number,
            'quantity_of_bags' => $this->quantity_of_bags,
            'w_jute_bag' => $this->w_jute_bag,
            'w_total' => $this->w_total,
            'w_truck' => $this->w_truck,
            'w_container' => $this->w_container,
            'w_gross' => $this->w_gross,
            'w_dunnage_dribag' => $this->w_dunnage_dribag,
            'w_tare' => $this->w_tare,
            'w_net' => $this->w_net,
            'note' => $this->note,
            'average_moisture' => $this->when(
                $this->relationLoaded('cuttingTests'),
                function () {
                    $tests = $this->cuttingTests->whereNotNull('moisture');
                    return $tests->isNotEmpty() ? round($tests->avg('moisture'), 1) : null;
                }
            ),
            'outturn_rate' => $this->when(
                $this->relationLoaded('cuttingTests'),
                function () {
                    $test = $this->cuttingTests->whereNotNull('outturn_rate')->first();
                    return $test ? $test->outturn_rate : null;
                }
            ),
            'bill' => $this->when(
                $this->relationLoaded('bill'),
                function () use ($request) {
                    return (new BillResource($this->bill))->toArray($request);
                }
            ),
            'cutting_tests' => $this->when(
                $this->relationLoaded('cuttingTests'),
                function () {
                    return CuttingTestResource::collection($this->cuttingTests)->resolve();
                }
            ),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}