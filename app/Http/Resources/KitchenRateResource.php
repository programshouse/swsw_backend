<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KitchenRateResource extends JsonResource
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
            'Order Number' => $this->order->number,
            'kitchen name' => $this->user->profile->name,
            'score' => $this->score,
            'rate name' => $this->userRate->name,
            'max score' => $this->userRate->max_score,
        ];
    }
}
