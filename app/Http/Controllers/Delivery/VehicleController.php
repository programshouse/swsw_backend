<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use App\Http\Resources\VehicleResource;

class VehicleController extends Controller
{
     public function index()
    {
        $all_vehicle = Vehicle::all();

        return response()->json([
            'data' => VehicleResource::collection($all_vehicle)
        ]);
    }
}
