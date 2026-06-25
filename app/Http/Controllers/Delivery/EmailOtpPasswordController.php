<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\DeliveryUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;

class EmailOtpPasswordController extends Controller
{
    public function sendOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
        ]);

        DB::beginTransaction();

        try {

            $delivery = DeliveryUser::where('phone', $data['phone'])->first();

            if (!$delivery) {
                return response()->json([
                    'status' => false,
                    'message' => 'المندوب غير موجود',
                ], 404);
            }

            if (empty($delivery->email)) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يوجد بريد إلكتروني مرتبط بهذا الحساب',
                ], 422);
            }

            $otp = (string) random_int(100000, 999999);

            $delivery->code = $otp;
            $delivery->code_expires_at = now()->addMinutes(10);
            $delivery->verified_at = null;
            $delivery->save();

            DB::commit();

            config([
                'mail.default' => 'smtp',
                'mail.mailers.smtp.transport' => 'smtp',
                'mail.mailers.smtp.host' => 'smtp.gmail.com',
                'mail.mailers.smtp.port' => 587,
                'mail.mailers.smtp.encryption' => 'tls',
                'mail.mailers.smtp.username' => 'programshouse2024@gmail.com',
                'mail.mailers.smtp.password' => 'iotm ppkh tdxj ziza',
                'mail.from.address' => 'programshouse2024@gmail.com',
                'mail.from.name' => 'SWSW',
            ]);

            Mail::to($delivery->email)->send(
                new SendOtpMail($otp, $delivery->name)
            );

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رمز التحقق إلى البريد الإلكتروني',
                'phone' => $delivery->phone,
                'email' => $delivery->email,
            ]);
        } catch (\Throwable $e) {

            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إرسال رمز التحقق',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function verifyOtp(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'otp' => ['required', 'digits:6'],
        ]);

        $delivery = DeliveryUser::where('phone', $data['phone'])->first();

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }

        if (empty($delivery->code) || empty($delivery->code_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد رمز تحقق صالح',
            ], 422);
        }

        if ((string) $delivery->code !== (string) $data['otp']) {
            return response()->json([
                'status' => false,
                'message' => 'رمز التحقق غير صحيح',
            ], 422);
        }

        if (now()->gt($delivery->code_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'انتهت صلاحية رمز التحقق',
            ], 422);
        }

        $delivery->verified_at = now();
        $delivery->save();

        return response()->json([
            'status' => true,
            'message' => 'تم التحقق من رمز التحقق بنجاح',
        ]);
    }

    public function resetPassword(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(6)],
        ]);

        DB::beginTransaction();

        try {
            $delivery = DeliveryUser::where('phone', $data['phone'])->first();

            if (!$delivery) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود',
                ], 404);
            }

            if (empty($delivery->verified_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'يجب التحقق من رمز التحقق أولاً',
                ], 422);
            }

            if (empty($delivery->code_expires_at) || now()->gt($delivery->code_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق',
                ], 422);
            }

            $delivery->password = Hash::make($data['password']);
            $delivery->code = null;
            $delivery->code_expires_at = null;
            $delivery->verified_at = null;
            $delivery->save();

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'تم إعادة تعيين كلمة المرور بنجاح',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء إعادة تعيين كلمة المرور',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
