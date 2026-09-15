<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $kitchenAddress = $this->kitchen?->user?->defaultAddress;
        return [
            'id' => $this->id,
            'number' => $this->number,
            'client_id' => $this->user_id,
            'client_name' => $this->user->name,
            'client_phone' => $this->user->phone,
            'client_address' => $this->userAddress?->full_address,
            'client_lat' => $this->userAddress?->lat,
            'client_lng' => $this->userAddress?->lng,
            'kitchen_id' => $this->kitchen_id,
            'kitchen_name' => $this->kitchen->name,
            'kitchen_address' => $kitchenAddress?->full_address,
            'kitchen_lat' => $kitchenAddress?->lat,
            'kitchen_lng' => $kitchenAddress?->lng,
            'kitchen_phone' => $this->kitchen->phone,
            'payment_method' => $this->payment_status ?? null,
            'distance_km' => $this->distance_km ?? null,
            //    'payment_status'=>$this->payment_status ?? null,
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            'receive_date' => $this->receive_date,
            'receive_time' => $this->receive_time,
            'items' => OrderItemsResource::collection($this->items),
        ];
    }
}
