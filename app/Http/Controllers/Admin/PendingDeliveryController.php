<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryUser;
use App\Models\PendingDelivery;
use Illuminate\Support\Facades\Storage;

class PendingDeliveryController extends Controller
{

    public function pending()
    {
        $deliveries = PendingDelivery::where('status', 'pending')
            ->latest()
            ->get();

        return view(
            'admin.delivery.profile',
            compact('deliveries')
        );
    }

    public function accept($id)
    {
        $delivery = PendingDelivery::findOrFail($id);

        $delivery_user = DeliveryUser::findOrFail($delivery->delivery_user_id);

        // delete old image 
        if ($delivery_user->image) {
            Storage::disk('public')->delete($delivery_user->image);
        }

        $delivery_user->update([
            'name' => $delivery->name,
            'email' => $delivery->email,
            'phone' => $delivery->phone,
            'birthdate' => $delivery->birthdate,
            'government_id' => $delivery->government_id,
            'area_id' => $delivery->area_id,
            'shift_id' => $delivery->shift_id,
            'type' => $delivery->type,
            'has_vehicle' => $delivery->has_vehicle,
            'vehicle_id' => $delivery->vehicle_id,
            'vehicle_type' => $delivery->vehicle_type,
            'image' => $delivery->image
        ]);

        $delivery->update([
            'status' => 'approved',
        ]);


        return response()->json([
            'status' => true,
            'message' => 'Delivery Profile Update approved successfully',
            'data' => $delivery
        ]);
    }

    public function reject($id)
    {
        $delivery = PendingDelivery::findOrFail($id);

        DeliveryUser::findOrFail($delivery->delivery_user_id);

        // delete image 
        if ($delivery->image) {
            Storage::disk('public')->delete($delivery->image);
        }

        $delivery->update([
            'status' => 'rejected'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Delivery Profile Update rejected successfully',
            'data' => null
        ]);
    }
}
