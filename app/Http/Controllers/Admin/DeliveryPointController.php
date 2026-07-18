<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPoint;
use App\Models\DeliveryUser;
use App\Models\PointTransaction;


class DeliveryPointController extends Controller
{
      public function index()
    {
        $delivery_points = PointTransaction::query()
            ->with([
                'owner',
                'reference',
            ])
            ->whereHasMorph(
                'owner',
                [DeliveryUser::class]
            )
            ->latest()
            ->get();

        return view(
            'admin.delivery_points.index',
            compact('delivery_points')
        );
    }

    public function destroy(PointTransaction $deliveryPoint)
    {
        if (!$deliveryPoint->owner instanceof DeliveryUser) {
            abort(404);
        }

        $deliveryPoint->delete();

        return redirect()
            ->route('admin.delivery.points.index')
            ->with('success', 'تم حذف عملية النقاط بنجاح');
    }
}
