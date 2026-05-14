<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserAddressResource;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardClientREsource extends JsonResource
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
            'name' => $this->name ,
            "phone" => $this->phone ,
            "email" => $this->email ,
            'address' => UserAddressResource::collection($this->address ),
            'orders' => OrderResource::collection($this->orders),
            'created_at' => $this->created_at
        ];
    }
}
