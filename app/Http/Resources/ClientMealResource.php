<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientMealResource extends JsonResource
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
            'description' => $this->description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'available_delivery_today' => boolval($this->available_delivery_today),
            'recipe' => $this->recipe,
            'image' => $this->image ? config('app.url') . '/storage/' . $this->image : null,
            'kitchen_logo' => $this->kitchen->logo ? config('app.url') . '/storage/' . $this->kitchen->logo : null,
            'kitchen_cover' => $this->kitchen->cover ? config('app.url') . '/storage/' . $this->kitchen->cover : null,
            'kitchen_name' => $this->kitchen->name,
            'category_name' => $this->category->name,
            'kitchen_id' => $this->kitchen->id ,
            "category_id" => $this->category_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'preparation_time' => $this->preparation_time,
            'chef_name' => $this->kitchen->user->name ,
            'kitchen_open_status' => $this->kitchen->open_status,
            'availability' => boolval($this->availability)
        ];
    }
}
