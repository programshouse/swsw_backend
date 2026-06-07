<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\AreaResource;
use App\Models\Government;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index(Government $government)
    {

        $government_areas = $government->areas()->where('status', true)->get();

        if ($government_areas->isEmpty()) {
            return response()->json([
                'message' => 'لا يوجد مناطق نشطة في المحافظة',
                'data' => null
            ]);
        };
        return response()->json([
            'data' => AreaResource::collection($government_areas)
        ]);
    }
}
