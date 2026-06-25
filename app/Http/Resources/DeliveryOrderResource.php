<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'order' => new OrderDetailResource($this->order),
            "delivery_has_multiple_active_orders" => $request->attributes->get('delivery_has_multiple_active_orders'),
        ];
    }
}
