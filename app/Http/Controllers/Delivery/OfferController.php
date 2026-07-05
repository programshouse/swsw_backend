<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;
use App\Models\pointTransactions;

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



///////aplly offer

    public function takeOffer($offerId)
{
    $delivery = auth('api_delivery')->user();

    $offer = Offer::where('is_active', 1)->findOrFail($offerId);

    $delivery->pointTransactions()->create([
        'source' => 'delivery_offer',
        'points' => $offer->points,
        'reference_type' => Offer::class,
        'reference_id' => $offer->id,
        'notes' => 'Points from delivery offer',
    ]);

    return response()->json([
        'status' => true,
        'message' => 'Points added successfully',
        'points_added' => $offer->points,
        'total_points' => $delivery->fresh()->total_points,
    ]);
}
}
