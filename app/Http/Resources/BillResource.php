<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'bill_number' => $this->bill_number,
            'seller' => $this->seller,
            'buyer' => $this->buyer,
            'note' => $this->note,
            'containers_count' => $this->whenCounted('containers'),
            'final_samples_count' => $this->whenCounted('finalSamples'),
            'average_outurn' => $this->when(
                $this->relationLoaded('finalSamples'),
                function () {
                    $finalSamples = $this->finalSamples->whereNotNull('outturn_rate');
                    return $finalSamples->isNotEmpty() 
                        ? round($finalSamples->avg('outturn_rate'), 2) 
                        : null;
                }
            ),
            'containers' => ContainerResource::collection($this->whenLoaded('containers')),
            'final_samples' => CuttingTestResource::collection($this->whenLoaded('finalSamples')),
            'cutting_tests' => CuttingTestResource::collection($this->whenLoaded('cuttingTests')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}