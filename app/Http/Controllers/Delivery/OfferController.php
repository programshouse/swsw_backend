<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Offer;

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
}
