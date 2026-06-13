<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemsResource extends JsonResource
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
            'meal_id' => $this->meal_id,
            'quantity' => $this->quantity,
            'price_pre_piece' => $this->price,
            'total_price' => $this->price * $this->quantity,
            'meal_name' => $this->meal->name ?? null,
            'meal_image' => $this->meal->image ? config('app.url') . '/storage/' . $this->meal->image : null,
            'preparation_time' => $this->meal->preparation_time ?? null,
        ];
        
    }
}
