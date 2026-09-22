<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /*
         * استخدام القيم المحفوظة للطلبات الجديدة.
         * مع fallback للطلبات القديمة الموجودة قبل التعديل.
         */
        $settings = Setting::first();

        $itemsTotal = $this->subtotal !== null
            ? (float) $this->subtotal
            : $this->items->sum(function ($item) {
                return
                    (float) $item->price *
                    (int) $item->quantity;
            });

        $vatPercentage =
            $this->vat_percentage !== null
            ? (float) $this->vat_percentage
            : (float) ($settings?->vat_percentage ?? 0);

        $vatValue =
            $this->vat_value !== null
            ? (float) $this->vat_value
            : ($itemsTotal * $vatPercentage) / 100;

        $deliveryMeterPrice =
            $this->delivery_meter_price !== null
            ? (float) $this->delivery_meter_price
            : (float) (
                $settings?->delivery_meter_price ?? 0
            );

        $distanceKm =
            $this->distance_km !== null
            ? (float) $this->distance_km
            : $this->getLegacyDistance();

        $deliveryPrice =
            $this->delivery_price !== null
            ? (float) $this->delivery_price
            : $distanceKm * $deliveryMeterPrice;

        $discountValue =
            (float) ($this->discount_value ?? 0);

        /*
         * رسوم العميل موجودة داخل الإجمالي المخزن،
         * لكننا لا نضيف مفتاحًا جديدًا في الـ response.
         */
        $clientServiceFee =
            (float) ($this->client_service_fee ?? 0);

        $totalBeforeDiscount =
            $this->total_before_discount !== null
            ? (float) $this->total_before_discount
            : (
                $itemsTotal +
                $vatValue +
                $deliveryPrice +
                $clientServiceFee
            );

        /*
         * استخدام total المحفوظ بدل إعادة حسابه.
         */
        $finalTotal = $this->total !== null
            ? (float) $this->total
            : max(
                $totalBeforeDiscount - $discountValue,
                0
            );

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'kitchen_id' => $this->kitchen_id,

            'items_total' => round($itemsTotal, 2),

            'vat_percentage' => round(
                $vatPercentage,
                2
            ),

            'vat_value' => round(
                $vatValue,
                2
            ),

            'distance_km' => round(
                $distanceKm,
                2
            ),

            'delivery_meter_price' => round(
                $deliveryMeterPrice,
                2
            ),

            'delivery_price' => round(
                $deliveryPrice,
                2
            ),

            'discount_value' => round(
                $discountValue,
                2
            ),

            'total_before_discount' => round(
                $totalBeforeDiscount,
                2
            ),

            'total' => round(
                $finalTotal,
                2
            ),

            'number' => $this->number,
            'status' => $this->status,
            'created_at' => $this->created_at
                ?->timezone('Africa/Cairo')
                ?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at,

            'client_name' => $this->user?->name,
            'client_phone' => $this->user?->phone,

            'kitchen_name' => $this->kitchen?->name,

            'kitchen_logo' => $this->kitchen?->logo
                ? config('app.url') .
                '/storage/' .
                $this->kitchen->logo
                : null,

            'items' => OrderItemsResource::collection(
                $this->items
            ),

            'estimated_time' =>
            $this->getEstimatedTimeAttribute(
                $this->items
            ),

            'receive_date' => $this->receive_date,
            'receive_time' => $this->receive_time,

            'book_for_later' =>
            (bool) $this->book_for_later,

            'cancel_date' => $this->cancel_date,
            'payment_method' => $this->payment_method,
        ];
    }

    private function getLegacyDistance(): float
    {
        $clientAddress =
            $this->userAddress ??
            $this->user?->defaultAddress;

        $kitchenAddress =
            $this->kitchen?->user?->defaultAddress;

        if (
            !$clientAddress ||
            !$kitchenAddress ||
            $clientAddress->lat === null ||
            $clientAddress->lng === null ||
            $kitchenAddress->lat === null ||
            $kitchenAddress->lng === null
        ) {
            return 0;
        }

        return $this->calculateDistance(
            (float) $clientAddress->lat,
            (float) $clientAddress->lng,
            (float) $kitchenAddress->lat,
            (float) $kitchenAddress->lng
        );
    }

    private function calculateDistance(
        float $lat1,
        float $lng1,
        float $lat2,
        float $lng2
    ): float {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) *
            cos(deg2rad($lat2)) *
            sin($dLng / 2) *
            sin($dLng / 2);

        $c = 2 * atan2(
            sqrt($a),
            sqrt(1 - $a)
        );

        return round(
            $earthRadius * $c,
            2
        );
    }

    public function getEstimatedTimeAttribute($items)
    {
        $times = [];

        foreach ($items as $item) {
            if ($item->meal) {
                $times[] =
                    (int) $item->meal->preparation_time *
                    (int) $item->quantity;
            }
        }

        return !empty($times)
            ? max($times)
            : 0;
    }
}
