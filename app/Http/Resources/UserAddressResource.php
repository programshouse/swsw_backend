<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserAddressResource extends JsonResource
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
            'full_address' => $this->full_address ,
            "phone" => $this->phone ,
            "is_default" => boolval($this->is_default) ,
            'government' => $this->government->name ,
            'area' => $this->area->name
        ];
    }
}
