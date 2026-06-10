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
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'kitchen_id' => $this->kitchen_id,
            'address' => $this->address,
            'total' => $this->total,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'client_name' => $this->user->name,
            'client_phone' => $this->user->phone,
            'kitchen_name' => $this->kitchen->name,
            'receive_date' => $this->receive_date,
            'receive_time' => $this->receive_time, 
        ];
    }
}
