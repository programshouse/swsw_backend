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
    public function offers()
    {
        $offers = Offer::where('is_active', 1)
            ->get()
            ->map(function ($offer) {
                return [
                    'id' => $offer->id,
                    'name' => $offer->name,
                    'description' => $offer->description,
                    'points' => $offer->points,
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

    return response()->json([
        'status' => true,
        'message' => 'Offer applied successfully and waiting for admin approval',
        'data' => $requestOffer,
    ]);
}
}
