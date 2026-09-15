<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\DeliveryOfferRequest;
use App\Models\pointTransactions;
use Illuminate\Http\JsonResponse;

class OfferController extends Controller
{
    public function offers(Request $request)
    {
        $delivery = $request->user();

        $offers = Offer::where('is_active', 1)
            ->get()
            ->map(function ($offer) use ($delivery) {

                $status = DeliveryOfferRequest::where(
                    'delivery_user_id',
                    $delivery->id
                )
                    ->where('offer_id', $offer->id)
                    ->value('status') ?? 'not_requested';

                return [
                    'id' => $offer->id,
                    'name' => $offer->name,
                    'description' => $offer->description,
                    'points' => $offer->points,
                    'status' => $status,
                ];
            });

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $offers,
        ]);
    }








    public function applyOffer(Request $request, Offer $offer): JsonResponse
    {
        $delivery = auth('api_delivery')->user();

        if (!$offer->is_active) {
            return response()->json([
                'status' => false,
                'message' => 'Offer is not active',
            ], 400);
        }

        $exists = DeliveryOfferRequest::where('delivery_user_id', $delivery->id)
            ->where('offer_id', $offer->id)
            ->first();

        if ($exists) {
            return response()->json([
                'status' => false,
                'message' => 'You already applied to this offer',
                'request_status' => $exists->status,
            ], 400);
        }

        $requestOffer = DeliveryOfferRequest::create([
            'delivery_user_id' => $delivery->id,
            'offer_id' => $offer->id,
            'status' => 'pending',
            'points' => $offer->points,
        ]);

        $lang = $request->header('lang', 'en');

        $message = $lang === 'ar'
            ? 'تم إرسال العرض بنجاح، وهو الآن في انتظار موافقة الإدارة.'
            : 'Offer applied successfully. It is now waiting for admin approval.';

        return response()->json([
            'status' => true,
            'message' =>  $message,
            'data' => $requestOffer,
        ]);
    }
}
