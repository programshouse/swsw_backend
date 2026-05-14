<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MealResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ,
            'name' => $this->name,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'price' => $this->price,
            'available_delivery_today' => boolval($this->available_delivery_today),
            'recipe' => $this->recipe,
            'approved' => $this->approved ,
            'image' => $this->image ? config('app.url') . '/storage/' . $this->image : null,
            'kitchen_name' => $this->kitchen->name ,
            'kitchen_phone' => $this->kitchen->phone ,
            'category_name' => $this->category->name ,
            "category_id" => $this->category_id,
            'created_at' => $this->created_at ,
            'updated_at' => $this->updated_at ,
            'preparation_time' => $this->preparation_time,
            'availability' => $this->availability
        ];
    }
}
