<?php

namespace App\Http\Resources;

use App\Http\Resources\MealResource;
use App\Http\Resources\OrderItemsResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
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
            'user_id' => $this->user_id,
            'kitchen_id' => $this->kitchen_id,
            'address' => $this->userAddress,
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_name' => $this->user->name,
            'client_phone' => $this->user->phone,
            'kitchen_name' => $this->kitchen->name,
            'kitchen_logo' => $this->kitchen->logo ? config('app.url') . '/storage/' . $this->kitchen->logo : null,
            'items' => OrderItemsResource::collection($this->items),
            'estimated_time' => $this->getEstimatedTimeAttribute($this->items),
            'receive_date' => $this->receive_date,
            'receive_time' => $this->receive_time,
            'book_for_later' => boolval($this->book_for_later),
            'cancel_date' => $this->cancel_date
        ];
    }

    public function getEstimatedTimeAttribute($items)
    {
        $times = [];
        foreach ($items as $item) {
            $times[] = $item->meal->preparation_time * $item->quantity;
        }
        return max($times);
    }
}
