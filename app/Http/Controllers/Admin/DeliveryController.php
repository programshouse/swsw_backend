<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryUser;
use App\Models\Level;
use Illuminate\Support\Facades\Hash;

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

        $delivery->update([
            'level_id' => $request->level_id
        ]);

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', 'Delivery Promoted Successfully');
    }

    public function generate_delivery_user_forget_password_code(Request $request, $id)
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

    public function onBreak(string $id)
    {
        $delivery = DeliveryUser::findOrFail($id);

        $is_break = $delivery->is_break;

        if ($is_break == 1) {

            $delivery->update([
                'is_break' => 0
            ]);
            $message = "Delivery {$delivery->name} Is Now Working";

        } else {
            
            $delivery->update([
                'is_break' => 1
            ]);
            $message = "Delivery {$delivery->name} Is Now On Break";
        }

        return redirect()
            ->route('admin.delivery.approved')
            ->with('success', $message);
    }
}
