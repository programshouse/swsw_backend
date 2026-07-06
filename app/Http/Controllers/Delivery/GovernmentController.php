<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\GovernmentResource;
use App\Models\Government;
use Illuminate\Http\Request;

class GovernmentController extends Controller
{
    
    public function index()
    {
        $all_governments =  Government::where('is_active', true)->get();
       // 

        return response()->json([
            'data' => GovernmentResource::collection($all_governments)
        ]);
    }
}
