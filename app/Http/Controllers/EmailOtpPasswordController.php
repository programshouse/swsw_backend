<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Mail\SendOtpMail;
use App\Models\User;
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

            $user = User::where('phone', $data['phone'])->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود',
                ], 404);
            }

            if (empty($user->email)) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يوجد بريد إلكتروني مرتبط بهذا الحساب',
                ], 422);
            }

            $otp = (string) random_int(100000, 999999);

            $user->code = $otp;
            $user->code_expires_at = now()->addMinutes(10);
            $user->verified_at = null;
            $user->save();

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

            Mail::to($user->email)->send(
                new SendOtpMail($otp, $user->name)
            );

            return response()->json([
                'status' => true,
                'message' => 'تم إرسال رمز التحقق إلى البريد الإلكتروني',
                'phone' => $user->phone,
                'email' => $user->email,
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

        $user = User::where('phone', $data['phone'])->first();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }

        if (empty($user->code) || empty($user->code_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'لا يوجد رمز تحقق صالح',
            ], 422);
        }

        if ((string) $user->code !== (string) $data['otp']) {
            return response()->json([
                'status' => false,
                'message' => 'رمز التحقق غير صحيح',
            ], 422);
        }

        if (now()->gt($user->code_expires_at)) {
            return response()->json([
                'status' => false,
                'message' => 'انتهت صلاحية رمز التحقق',
            ], 422);
        }

        $user->verified_at = now();
        $user->save();

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
            $user = User::where('phone', $data['phone'])->first();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'المستخدم غير موجود',
                ], 404);
            }

            if (empty($user->verified_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'يجب التحقق من رمز التحقق أولاً',
                ], 422);
            }

            if (empty($user->code_expires_at) || now()->gt($user->code_expires_at)) {
                return response()->json([
                    'status' => false,
                    'message' => 'انتهت صلاحية رمز التحقق',
                ], 422);
            }

            $user->password = Hash::make($data['password']);
            $user->code = null;
            $user->code_expires_at = null;
            $user->verified_at = null;
            $user->save();

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
