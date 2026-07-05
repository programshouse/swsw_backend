<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Setting;

class DeliveryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
         $settings = Setting::select(
            'user_app_link',
            'kitchen_app_link',
            'delivery_app_link'
        )->first();

        return [
            "id" => $this->id,
            "name" => $this->name,
              'code '=> $this->code ??null ,
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
             'app_links' => [
                'user_app_link' => $settings?->user_app_link,
                'kitchen_app_link' => $settings?->kitchen_app_link,
                'delivery_app_link' => $settings?->delivery_app_link,
            ],
        ];
    }
}
