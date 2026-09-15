<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenPackageResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->desc,
            'features' => $this->features ?? [],
            'price' => (float) $this->price,
            'duration' => $this->duration,
            'active' => (bool) $this->active,
            'meals_limit' => $this->meals_limit,
            'orders_limit' => $this->orders_limit,
        ];
    }
}
