<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardKitchenProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subscription =
            $this->currentPackageSubscription;

        $package =
            $subscription?->package;

        return [

            'id' => $this->id,

            'name' => $this->name,

            'phone' => $this->phone,

            'whatsapp' => $this->whatsapp,

            'facebook' => $this->facebook,

            'location' => $this->location,

            'statue' => $this->statue,

            'rejected_note' =>
                $this->rejected_note,

            'working_time_start' =>
                $this->working_time_start,

            'working_time_end' =>
                $this->working_time_end,

            'have_delivery' =>
                (bool) $this->have_delivery,

            'logo' => $this->logo
                ? config('app.url')
                    . '/storage/'
                    . $this->logo
                : null,

            'cover' => $this->cover
                ? config('app.url')
                    . '/storage/'
                    . $this->cover
                : null,

            'work_days' =>
                new WorkDayResource(
                    $this->work_day
                ),

            'meals' =>
                MealResource::collection(
                    $this->meals
                ),

            'government' =>
                $this->government['name']
                    ?? null,

            'area' =>
                $this->area['name']
                    ?? null,

            'open_status' =>
                $this->open_status,

            'have_star' =>
                (bool) $this->have_star,

            'chef_name' =>
                $this->user?->name,

            'orders' =>
                OrderResource::collection(
                    $this->orders
                ),

            'wallet' =>
                $this->user?->wallet,


            /*
            |--------------------------------------------------------------------------
            | Current Package
            |--------------------------------------------------------------------------
            */

            'has_active_package' =>
                $subscription !== null,

            'package' => $subscription
                ? [

                    'subscription_id' =>
                        $subscription->id,

                    'package_id' =>
                        $subscription
                            ->kitchen_package_id,

                    'name' =>
                        $subscription
                            ->package_name,

                    'price' =>
                        (float) $subscription
                            ->package_price,

                    'duration' =>
                        (int) $subscription
                            ->package_duration,

                    'duration_unit' =>
                        $subscription
                            ->duration_unit,

                    'status' =>
                        $subscription->status,

                    'starts_at' =>
                        $subscription
                            ->starts_at
                            ?->toISOString(),

                    'expires_at' =>
                        $subscription
                            ->expires_at
                            ?->toISOString(),

                    /*
                     * Limits من الباقة الأصلية
                     */

                    'meals_limit' =>
                        (int) (
                            $package?->meals_limit
                            ?? 0
                        ),

                    'orders_limit' =>
                        (int) (
                            $package?->orders_limit
                            ?? 0
                        ),

                ]
                : null,
        ];
    }
}