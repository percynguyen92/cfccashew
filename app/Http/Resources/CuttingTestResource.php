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
        $formattedDefectNut = null;
        $formattedDefectKernel = null;

        if ($this->w_defective_nut !== null) {
            $formattedDefectNut = sprintf('%s/%s', $this->w_defective_nut, number_format((float) ($this->w_defective_nut / 2)));
        }

        if ($this->w_defective_kernel !== null) {
            $formattedDefectKernel = sprintf('%s/%s', $this->w_defective_kernel, number_format((float) ($this->w_defective_kernel / 2)));
        }

        return [
            'id' => $this->id !== null ? (int) $this->id : null,
            'bill_id' => $this->bill_id !== null ? (int) $this->bill_id : null,
            'container_id' => $this->container_id !== null ? (int) $this->container_id : null,
            'type' => $this->type !== null ? (int) $this->type : null,
            'type_label' => $typeEnum?->label(),
            'moisture' => $this->moisture,
            'sample_weight' => $this->sample_weight !== null ? (int) $this->sample_weight : null,
            'nut_count' => $this->nut_count !== null ? (int) $this->nut_count : null,
            'w_reject_nut' => $this->w_reject_nut !== null ? (int) $this->w_reject_nut : null,
            'w_defective_nut' => $this->w_defective_nut !== null ? (int) $this->w_defective_nut : null,
            'w_defective_kernel' => $this->w_defective_kernel !== null ? (int) $this->w_defective_kernel : null,
            'w_good_kernel' => $this->w_good_kernel !== null ? (int) $this->w_good_kernel : null,
            'w_sample_after_cut' => $this->w_sample_after_cut !== null ? (int) $this->w_sample_after_cut : null,
            'outturn_rate' => $this->outturn_rate,
            'moisture_formatted' => $this->moisture ? number_format((float) $this->moisture, 1) : null,
            'outturn_rate_formatted' => $this->outturn_rate ? number_format((float) $this->outturn_rate, 2) : null,
            'defective_nut_formatted' => $formattedDefectNut,
            'defective_kernel_formatted' => $formattedDefectKernel,
            'note' => $this->note,
            'is_final_sample' => $isFinalSample && is_null($this->container_id),
            'is_container_test' => $isContainerTest && !is_null($this->container_id),
            'bill' => $this->whenLoaded('bill', function () use ($request) {
                return (new BillResource($this->bill))->toArray($request);
            }),
            'container' => $this->whenLoaded('container', function () use ($request) {
                return (new ContainerResource($this->container))->toArray($request);
            }),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

