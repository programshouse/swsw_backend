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

    private function imageUrl($path)
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'public/')) {
            return config('app.url') . '/' . $path;
        }

        return config('app.url') . '/storage/' . $path;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'meal_id' => $this->meal_id,
            'quantity' => $this->quantity,
            'price_pre_piece' => $this->price,
            'total_price' => $this->price * $this->quantity,
            'meal_name' => $this->meal->name ?? null,
            'meal_image' => $this->imageUrl($this->meal?->image),


            'preparation_time' => $this->meal->preparation_time ?? null,
        ];
    }
}
