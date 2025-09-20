<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\CuttingTestType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CuttingTestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $typeEnum = $this->type !== null
            ? CuttingTestType::tryFrom((int) $this->type)
            : null;

        $isFinalSample = $typeEnum?->isFinalSample() ?? false;
        $isContainerTest = $typeEnum?->isContainerTest() ?? false;

        return [
            'id' => $this->id,
            'bill_id' => $this->bill_id,
            'container_id' => $this->container_id,
            'type' => $this->type,
            'type_label' => $typeEnum?->label(),
            'moisture' => $this->moisture,
            'sample_weight' => $this->sample_weight,
            'nut_count' => $this->nut_count,
            'w_reject_nut' => $this->w_reject_nut,
            'w_defective_nut' => $this->w_defective_nut,
            'w_defective_kernel' => $this->w_defective_kernel,
            'w_good_kernel' => $this->w_good_kernel,
            'w_sample_after_cut' => $this->w_sample_after_cut,
            'outturn_rate' => $this->outturn_rate,
            'note' => $this->note,
            'defective_ratio' => $this->when(
                $this->w_defective_nut && $this->w_defective_kernel,
                function () {
                    $ratio = $this->w_defective_nut > 0
                        ? round($this->w_defective_kernel / $this->w_defective_nut, 1)
                        : 0;

                    return [
                        'defective_nut' => $this->w_defective_nut,
                        'defective_kernel' => $this->w_defective_kernel,
                        'ratio' => $ratio,
                        'formatted' => "{$this->w_defective_nut}/{$ratio}",
                    ];
                }
            ),
            'is_final_sample' => $isFinalSample && is_null($this->container_id),
            'is_container_test' => $isContainerTest && !is_null($this->container_id),
            'bill' => new BillResource($this->whenLoaded('bill')),
            'container' => new ContainerResource($this->whenLoaded('container')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

