<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientRateResource;
use App\Http\Resources\KitchenRateResource;
use App\Models\RateStore;
use Illuminate\Http\Request;

class RateStoreController extends Controller
{

    public function rates_by_kitchen()
    {
        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'kitchen')->with('order')->get();

        return response()->json([
            'data' => KitchenRateResource::collection($all_delivery_rates),
        ]);
    }


    public function rates_by_client()
    {
        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'client')->with('order')->get();

        return response()->json([
            'data' => ClientRateResource::collection($all_delivery_rates),
        ]);
    }


    
    public function store_rates_by_kitchen()
    {
        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'client')->with('order')->get();

        return response()->json([
            'data' => ClientRateResource::collection($all_delivery_rates),
        ]);
    }
}
