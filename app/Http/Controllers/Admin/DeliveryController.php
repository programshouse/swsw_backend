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
use App\Models\Wallet;
use App\Models\Area;
use Illuminate\Support\Facades\DB;


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
        DB::beginTransaction();

        try {
            $delivery = DeliveryUser::findOrFail($id);

            if ($delivery->status !== 'pending') {
                DB::rollBack();

                return response()->json([
                    'status' => false,
                    'message' => 'Already processed'
                ], 400);
            }

            do {
                $shiftCode = random_int(1000, 9999);
                $code = random_int(1000, 9999);
            } while (
                DeliveryUser::where('shift_code', $shiftCode)->exists() ||
                DeliveryUser::where('code', $code)->exists()
            );

            $delivery->update([
                'status' => 'approved',
                'shift_code' => $shiftCode,
                'code' => $code,
            ]);

            Wallet::firstOrCreate(
                [
                    'owner_id' => $delivery->id,
                    'owner_type' => 'delivery',
                ],
                [
                    'available_amount' => 0,
                    'pending_amount' => 0,
                ]
            );

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Delivery approved successfully',
                'data' => $delivery->fresh(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
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


  public function approved(Request $request)
{
    $deliveries = DeliveryUser::with([
            'level',
            'government',
            'area'
        ])
        ->where('status', 'approved')

        // فلتر اسم الدليفري
        ->when($request->name, function ($query) use ($request) {
            $query->where('name', 'like', '%' . $request->name . '%');
        })

        // فلتر المنطقة
        ->when($request->area_id, function ($query) use ($request) {
            $query->where('area_id', $request->area_id);
        })

        ->latest()
        ->get();


    $levels = Level::all();
    $points = Point::get();

    // جلب المناطق للـ select
    $areas = Area::all();


    return view(
        'admin.delivery.approved',
        compact(
            'deliveries',
            'levels',
            'points',
            'areas'
        )
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

        /*
    |--------------------------------------------------------------------------
    | إنهاء الراحة
    |--------------------------------------------------------------------------
    */

        if ((bool) $delivery->is_break) {

            $delivery->update([
                'is_break' => false,
                'break_time' => null,
                'break_started_at' => null,
            ]);

            return redirect()
                ->route('admin.delivery.approved')
                ->with(
                    'success',
                    "تم إنهاء راحة الدليفري {$delivery->name} وأصبح يعمل الآن"
                );
        }

        /*
    |--------------------------------------------------------------------------
    | بدء الراحة
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'break_time' => [
                'required',
                'integer',
                'min:1',
                'max:1440',
            ],
        ], [
            'break_time.required' => 'يرجى إدخال مدة الراحة.',
            'break_time.integer' => 'مدة الراحة يجب أن تكون رقمًا صحيحًا.',
            'break_time.min' => 'مدة الراحة يجب ألا تقل عن دقيقة واحدة.',
            'break_time.max' => 'مدة الراحة يجب ألا تزيد عن 1440 دقيقة.',
        ]);

        $delivery->update([
            'is_break' => true,
            'break_time' => $validated['break_time'],
            'break_started_at' => now(),
        ]);

        return redirect()
            ->route('admin.delivery.approved')
            ->with(
                'success',
                "تم بدء راحة الدليفري {$delivery->name} لمدة {$validated['break_time']} دقيقة"
            );
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




    public function toggleStatus(DeliveryUser $delivery)
    {
        if ($delivery->status === 'inactive') {

            $delivery->update([
                'status' => 'approved',
                'is_break' => 0,
            ]);

            $message = 'تم تفعيل الدليفري بنجاح';
        } else {

            $delivery->update([
                'status' => 'inactive',
                'is_break' => 0,
            ]);

            $delivery->tokens()->delete();

            $message = 'تم تعطيل الدليفري بنجاح';
        }

        return back()->with('success', $message);
    }
}
