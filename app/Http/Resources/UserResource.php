<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Http\Resources\AreaResource;
use App\Http\Resources\GovernmentResource;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id ,
            "name" => $this->name ,
            "email" => $this->email ,
            "phone" => $this->phone ,
            "role" => $this->role ,
            "status" => $this->status ,
            "created_at" => $this->created_at ,
            "government" => new GovernmentResource($this->government) ,
            "area" => new AreaResource($this->area) ,
            'profile' => new KitchenProfileResource($this->profile)
        ];
    }
}
