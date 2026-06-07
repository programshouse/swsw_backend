<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShiftResource;
use Illuminate\Http\Request;
use App\Models\DeliveryShiftLog;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $all_shifts = Shift::all();

        return response()->json([
            'data' => ShiftResource::collection($all_shifts)
        ]);
    }

    public function startShift(Request $request)
    {
        $delivery = auth('delivery-api')->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // check if already active shift
        $activeShift = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
            ->where('status', 'active')
            ->first();

        if ($activeShift) {
            return response()->json([
                'status' => false,
                'message' => 'Shift already started'
            ], 400);
        }

        $shift = DeliveryShiftLog::create([
            'delivery_user_id' => $delivery->id,
            'start_time' => now(),
            'status' => 'active',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shift started successfully',
            'data' => $shift
        ]);
    }


    public function endShift(Request $request)
    {
        $delivery = auth('delivery-api')->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        $shift = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
            ->where('status', 'active')
            ->first();

        if (!$shift) {
            return response()->json([
                'status' => false,
                'message' => 'No active shift found'
            ], 400);
        }

        $shift->update([
            'end_time' => now(),
            'status' => 'finished'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shift ended successfully',
            'data' => $shift
        ]);
    }
}
