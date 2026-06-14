<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientRateResource extends JsonResource
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
            'Date' => $this->order->receive_date,
            'Time' => $this->order->receive_time,
            'kitchen name' => $this->user->profile->name,
            'user_address' => $this->order->userAddress?->full_address,
            'score' => $this->score,
            'rate name' => $this->userRate->name,
            'max score' => $this->userRate->max_score,
        ];
    }
}
