<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryPoint;


class DeliveryPointController extends Controller
{
      public function index()
      {
            $delivery_points = DeliveryPoint::get();

            return view('admin.delivery_points.index', compact('delivery_points'));
      }

      public function destroy(DeliveryPoint $deliveryPoint)
      {
            $deliveryPoint->delete();

            return redirect()
                  ->route('admin.delivery.points.index')
                  ->with('success', 'Delivery Points Deleted Successfully');
      }
}
