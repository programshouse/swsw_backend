<?php

namespace App\Http\Resources;

use App\Models\Setting;
use App\Http\Resources\UserAddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientProfileResource extends JsonResource
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
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code ?? null,
            "phone" => $this->phone,
            "email" => $this->email,
            'address' => UserAddressResource::collection($this->address),
            'app_links' => [
                'user_app_link' => $settings?->user_app_link,
                'kitchen_app_link' => $settings?->kitchen_app_link,
                'delivery_app_link' => $settings?->delivery_app_link,
            ],
            'created_at' => $this->created_at
        ];
    }
}
