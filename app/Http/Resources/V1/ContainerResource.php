<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\V1\CuttingTestResource;

class ContainerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'truck' => $this->truck,
            'containerNumber' => $this->container_number,
            'quantityOfBags' => $this->quantity_of_bags,
            'wJuteBag' => $this->w_jute_bag,
            'wTotal' => $this->w_total,
            'wTruck' => $this->w_truck,
            'wContainer' => $this->w_container,
            'wGross' => $this->w_gross,
            'wDunnageDribag' => $this->w_dunnage_dribag,
            'wTare' => $this->w_tare,
            'wNet' => $this->w_net,
            'cuttingTest' => new CuttingTestResource($this->whenLoaded('cuttingTest')),
        ];
    }
}