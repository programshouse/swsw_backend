<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryUser;
use App\Models\Level;

class DeliveryController extends Controller
{
    public function pending()
    {
        $deliveries = DeliveryUser::where('status', 'pending')
            ->latest()
            ->get();

        return view(
            'admin.delivery.pending',
            compact('deliveries')
        );
    }

    public function accept($id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        if ($delivery->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Already processed'
            ], 400);
        }

        $delivery->update([
            'status' => 'approved'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery approved successfully',
            'data' => $delivery
        ]);
    }

    public function reject($id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        if ($delivery->status !== 'pending') {
            return response()->json([
                'status' => false,
                'message' => 'Already processed'
            ], 400);
        }

        $delivery->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery rejected successfully',
            'data' => $delivery
        ]);
    }

    public function approved()
    {
        $deliveries = DeliveryUser::with('level')->where('status', 'approved')
            ->latest()
            ->get();

        $levels = Level::all();
        return view(
            'admin.delivery.approved',
            compact('deliveries', 'levels')
        );
    }

    public function promotion(Request $request, string $id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        $delivery_new_level = $delivery->update([
            'level_id' => $request->level_id
        ]);

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', 'Delivery Promoted Successfully');
    }
}
