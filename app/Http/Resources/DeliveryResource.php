<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            "phone" => $this->phone,
            "area" => new AreaResource($this->area),
            "birthdate" => $this->birthdate,
             
            "shift_id" => new ShiftResource($this->shift),
            "type" => $this->type,
            "has_vehicle" => $this->has_vehicle,
            "vehicle_id" => $this->vehicle_id,
            "vehicle_type" => $this->vehicle_type,
            "image" => $this->image,
        ];
    }
}
