<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\RateStoreResource;
use App\Models\RateStore;
use Illuminate\Http\Request;

class RateStoreController extends Controller
{

    public function index()
    {

        $all_delivery_rates = RateStore::where('rated_type', 'delivery')->where('rater_type', 'kitchen')->with('order')->get();

        return response()->json([
            'data' => RateStoreResource::collection($all_delivery_rates),
        ]);
    }

}
