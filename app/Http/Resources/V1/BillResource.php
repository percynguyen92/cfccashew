<?php

declare(strict_types=1);

namespace App\Http\Resources\V1;

use App\Http\Resources\V1\ContainerResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BillResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'billNumber' => $this->billNumber,
            'seller' => $this->seller,
            'buyer' => $this->buyer,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
            'containers' => ContainerResource::collection($this->whenLoaded('containers')),
            'cuttingTests' => CuttingTestResource::collection($this->whenLoaded('cuttingTests')),
        ];
    }
}