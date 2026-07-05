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
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'code'=>'nullable|int',
        ]);

        $delivery = $request->user();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

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
            'start_lat' => $request->lat,
            'start_lng' => $request->lng,
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
        $request->validate([
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        $delivery = $request->user();

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
            'end_lat' => $request->lat,
            'end_lng' => $request->lng,
            'status' => 'finished'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Shift ended successfully',
            'data' => $shift->fresh()
        ]);
    }


    public function updateLocation(Request $request)
{
    $request->validate([
        'lat' => 'required|numeric',
        'lng' => 'required|numeric',
    ]);

    $delivery = $request->user();

    $delivery->update([
        'current_lat' => $request->lat,
        'current_lng' => $request->lng,
        'last_location_at' => now(),
    ]);

    $activeShift = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
        ->where('status', 'active')
        ->latest()
        ->first();

    if ($activeShift) {
        $activeShift->update([
            'end_lat' => $request->lat,
            'end_lng' => $request->lng,
        ]);
    }

    return response()->json([
        'status' => true,
        'message' => 'Location updated successfully',
    ]);
}
}
