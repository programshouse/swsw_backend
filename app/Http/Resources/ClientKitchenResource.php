<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClientKitchenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    private function imageUrl($path)
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        if (str_starts_with($path, 'public/')) {
            return url($path);
        }

        return config('app.url') . '/storage/' . $path;
    }

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'working_time_start' => $this->working_time_start,
            'working_time_end' => $this->working_time_end,
            'logo' => $this->imageUrl($this->logo),
            'cover' => $this->imageUrl($this->cover),
            'work_days' => new WorkDayResource($this->work_day),
            'meals' => MealResource::collection($this->meals),
            'open_status' => $this->open_status,
            'have_star' => boolval($this->have_star),
            'government' => $this->government['name'] ?? null,
            'area' => $this->area['name'] ?? null,
            'chef_name' => $this->user->name
        ];
    }
}
