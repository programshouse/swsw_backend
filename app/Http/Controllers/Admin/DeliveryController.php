<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryUser;
use App\Models\Level;
use App\Models\Point;
use App\Models\DeliveryPoint;
use App\Models\DeliveryOrder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use App\services\PointService;


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

        do {
            $shiftCode = random_int(1000, 9999);
            $Code = random_int(1000, 9999);
        } while (
            DeliveryUser::where('shift_code', $shiftCode)->exists() ||
            DeliveryUser::where('code', $Code)->exists()
        );

        $delivery->update([
            'status' => 'approved',
            'shift_code' => $shiftCode,
            'code' => $Code,
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
        $points = Point::get();
        $this_month = Carbon::now()->format('F');

        return view(
            'admin.delivery.approved',
            compact('deliveries', 'levels', 'points', 'this_month')
        );
    }

    public function promotion(Request $request, string $id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        $delivery->update([
            'level_id' => $request->level_id
        ]);

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', 'Delivery Promoted Successfully');
    }

    public function generate_delivery_user_forget_password_code($id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        $code = rand(100000, 999999);

        $delivery->update([
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10),
            'password' => Hash::make($code)
        ]);

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', 'تم إنشاء كود تغيير كلمة المرور بنجاح')
            ->with('generated_code_user_id', $delivery->id)
            ->with('generated_code', $code);
    }

    public function onBreak(Request $request, string $id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        $is_break = $delivery->is_break;

        if ($is_break == 1) {

            $delivery->update([
                'is_break' => 0,
                'break_time' => 0,
            ]);

            $message = "Delivery {$delivery->name} Is Now Working";
        } else {

            $delivery->update([
                'is_break' => 1,
                'break_time' => $request->break_time,
                'break_started_at' => now()
            ]);

            $message = "Delivery {$delivery->name} Is Now On Break";
        }

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', $message);
    }

  public function addPoint(
    Request $request,
    string $id,
    PointService $pointService
) {
    $data = $request->validate([
        'point_id' => [
            'required',
            'integer',
            'exists:points,id',
        ],

        'notes' => [
            'nullable',
            'string',
            'max:1000',
        ],
    ], [
        'point_id.required' => 'يجب اختيار عدد النقاط',
        'point_id.integer' => 'اختيار النقاط غير صحيح',
        'point_id.exists' => 'اختيار النقاط غير موجود',
    ]);

    $delivery = DeliveryUser::findOrFail($id);

    $point = Point::findOrFail($data['point_id']);

    $pointsNumber = (int) $point->number;

    if ($pointsNumber <= 0) {
        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'عدد النقاط يجب أن يكون أكبر من صفر');
    }

    $pointService->add(
        owner: $delivery,
        points: $pointsNumber,
        source: 'admin_add',
        reference: $point,
        notes: $data['notes']
            ?? 'تمت إضافة النقاط بواسطة الأدمن'
    );

    return redirect()
        ->route('admin.delivery.approved')
        ->with(
            'success',
            "تمت إضافة {$pointsNumber} نقطة إلى {$delivery->name} بنجاح"
        );
}






    public function ordersIndex()
    {
        $orders = DeliveryOrder::with([
            'delivery',
            'order.user',
            'order.kitchen',
        ])
            ->latest()
            ->get();

        $points = Point::latest()->get();

        return view('admin.delivery.orders', compact('orders', 'points'));
    }

    public function addOrderPoints(Request $request)
    {
        $validated = $request->validate([
            'delivery_user_id' => 'required|exists:delivery_users,id',
            'order_id' => 'required|exists:orders,id',
            'point_id' => 'required|exists:points,id',
        ]);

        DeliveryPoint::create($validated);

        return back()->with('success', 'تم إضافة النقاط للدليفري بنجاح');
    }
}
