<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryUser;
use App\Models\PendingDelivery;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;


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
    DB::beginTransaction();

    try {
        $delivery = PendingDelivery::findOrFail($id);

        if (!$delivery->delivery_user_id) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد دليفري مرتبط بهذا الطلب',
            ], 422);
        }

        $deliveryUser = DeliveryUser::find($delivery->delivery_user_id);

        if (!$deliveryUser) {
            return response()->json([
                'status' => false,
                'message' => 'الدليفري الأصلي غير موجود',
            ], 404);
        }

        $updateData = [
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
        ];

        if (!empty($delivery->image)) {
            if ($deliveryUser->image && $deliveryUser->image !== $delivery->image) {
                Storage::disk('public')->delete($deliveryUser->image);
            }

            $updateData['image'] = $delivery->image;
        }

        $deliveryUser->update($updateData);

        $delivery->update([
            'status' => 'approved',
        ]);

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'تمت الموافقة على تعديل بيانات الدليفري بنجاح',
            'data' => $deliveryUser->fresh(),
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء الموافقة',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function reject($id)
{
    try {
        $delivery = PendingDelivery::findOrFail($id);

        $delivery->update([
            'status' => 'rejected',
        ]);

        return response()->json([
            'status' => true,
            'message' => 'تم رفض تعديل بيانات الدليفري بنجاح',
            'data' => null,
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => false,
            'message' => 'حدث خطأ أثناء الرفض',
            'error' => $e->getMessage(),
        ], 500);
    }
}

    
}
