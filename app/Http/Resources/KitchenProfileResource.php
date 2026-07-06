<?php

namespace App\Http\Resources;

use App\Models\Meal;
use Illuminate\Http\Request;
use App\Http\Resources\MealResource;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Setting;

class KitchenProfileResource extends JsonResource
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

        
            'id' => $this->id ,
            'name' => $this->name,
              'code '=> $this->code ??null,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'facebook' => $this->facebook,
            'location' => $this->location,
            'statue' => $this->statue,
            'rejected_note' => $this->rejected_note,
            'working_time_start' => $this->working_time_start,
            'working_time_end' => $this->working_time_end,
            'have_delivery' => $this->have_delivery,
            'logo' => $this->logo ? config('app.url') . '/storage/' . $this->logo : null,
            'cover' => $this->cover ? config('app.url') . '/storage/' . $this->cover : null,
            'work_days' => new WorkDayResource($this->work_day) ,
            'meals' => MealResource::collection($this->meals) ,
            'government' => $this->government['name_ar'] ?? null,
            'area' => $this->area['name_ar'] ?? null,
            'open_status' => $this->open_status,
            'have_star' => boolval($this->have_star),
            'chef_name' => $this->user->name ,
             'app_links' => [
                'user_app_link' => $settings?->user_app_link,
                'kitchen_app_link' => $settings?->kitchen_app_link,
                'delivery_app_link' => $settings?->delivery_app_link,
            ],
        ];
    }
}
