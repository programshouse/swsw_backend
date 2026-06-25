<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryUser;
use App\Models\DeliveryShiftLog;
use App\Http\Resources\DeliveryResource;
use App\Models\PendingDelivery;
use Illuminate\Support\Facades\Hash;
use App\Models\Level;


class AuthController extends Controller
{
  private function generateDeliveryCode(): string
{
    $lastDelivery = DeliveryUser::whereNotNull('code')
        ->orderByDesc('id')
        ->first();

    $nextNumber = 1;

    if ($lastDelivery && $lastDelivery->code) {
        $nextNumber = ((int) preg_replace('/[^0-9]/', '', $lastDelivery->code)) + 1;
    }

    return 'DE' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
}
    public function register(Request $request)
    {
        $request->validate([
             'code' => $this->generateDeliveryCode(),
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:delivery_users,email',
            'phone' => 'required|unique:delivery_users,phone',
            'birthdate' => 'required|date',
            'password' => 'required|min:6|confirmed',

            'government_id' => 'required|string|exists:governments,id',

            'level_id' => 'nullable|string|exists:levels,id',

            'area_id' => 'required|exists:areas,id',

            'shift_id' => 'required|exists:shifts,id',

            'type' => 'required|in:company,freelance',

            'has_vehicle' => 'required|boolean',
            'vehicle_id' => 'nullable|string|exists:vehicles,id',
            'vehicle_type' => 'nullable|in:car,motorcycle,bicycle',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
              'referral_code' => 'nullable|string|max:50',


        ]);


        $levelId = $request->level_id;

        if (!$levelId) {
            $defaultLevel = Level::where('is_default', true)->first();

            if (!$defaultLevel) {
                return response()->json([
                    'status' => false,
                    'message' => 'No default level configured.'
                ], 422);
            }

            $levelId = $defaultLevel->id;
        }

        $imagePath = null;



        if ($request->hasFile('image')) {

            $file = $request->file('image');
            $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

            $folder = "assets/delivery/images/";
            $path = public_path($folder);

            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $file->move($path, $fileName);

            $imagePath = "https://www.programshouse.com/swsw/public/" . $folder . $fileName;
        }

        $delivery = DeliveryUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'password' => bcrypt($request->password),
            'government_id' => $request->government_id,
            'level_id' => $levelId,
            'area_id' => $request->area_id,
            'shift_id' => $request->shift_id,
            'type' => $request->type,
            'has_vehicle' => $request->has_vehicle,
            'vehicle_id' => $request->vehicle_id,
            'vehicle_type' => $request->vehicle_type,
            'image' => $imagePath,
            'status' => 'pending',
             'referral_code' => $validated['referral_code'] ?? null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Registration successful. Waiting for admin approval.',
            'data' => $delivery
        ]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'password' => 'required',
        ]);

        $delivery = DeliveryUser::where('phone', $request->phone)->first();

        if (!$delivery || !Hash::check($request->password, $delivery->password)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        // ❌ منع الدخول لو مش مقبول
        if ($delivery->status !== 'approved') {
            return response()->json([
                'status' => false,
                'message' => 'Account not approved yet'
            ], 403);
        }

        // create token
        $token = $delivery->createToken('delivery-token')->plainTextToken;

        return response()->json([
            'status' => true,
            'message' => 'Login successful',
            'token' => $token,
            'data' => $delivery
        ]);
    }


    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ]);
    }


    public function profile(Request $request)
    {
        $delivery = $request->user();

        $delivery->load([
            'area',
            'shift',
            'level',
        ]);

        $levels = Level::get()->map(function ($level) use ($delivery) {
            return [
                'id' => $level->id,
                'name' => $level->name,
                'cash_money' => $level->cash_money,
                'km' => $level->km,
                'vehicle_id' => $level->vehicle_id,
                'is_default' => $level->is_default,
                'is_current' => $delivery->level_id == $level->id,
            ];
        });

        return response()->json([
            'status' => true,
            'data' => [
                'user' => [
                    new DeliveryResource($delivery)
                ],
                'levels' => $levels,
                'current_level_id' => $delivery->level->name,
            ]
        ]);
    }


   public function updateProfile(Request $request)
{
    $delivery = $request->user();

    $validated = $request->validate([
        'name' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:delivery_users,email,' . $delivery->id,
        'phone' => 'nullable|unique:delivery_users,phone,' . $delivery->id,
        'birthdate' => 'nullable|date',

        'government_id' => 'nullable|exists:governments,id',
        'area_id' => 'nullable|exists:areas,id',
        'shift_id' => 'nullable|exists:shifts,id',

        'type' => 'nullable|in:company,freelance',

        'has_vehicle' => 'nullable|boolean',
        'vehicle_id' => 'nullable|exists:vehicles,id',
        'vehicle_type' => 'nullable|string',

        'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ]);

    $pendingDelivery = PendingDelivery::where('delivery_user_id', $delivery->id)->first();

    $data = [
        'delivery_user_id' => $delivery->id,
        'name' => $request->filled('name') ? $request->name : ($pendingDelivery->name ?? $delivery->name),
        'email' => $request->filled('email') ? $request->email : ($pendingDelivery->email ?? $delivery->email),
        'phone' => $request->filled('phone') ? $request->phone : ($pendingDelivery->phone ?? $delivery->phone),
        'birthdate' => $request->filled('birthdate') ? $request->birthdate : ($pendingDelivery->birthdate ?? $delivery->birthdate),
        'government_id' => $request->filled('government_id') ? $request->government_id : ($pendingDelivery->government_id ?? $delivery->government_id),
        'area_id' => $request->filled('area_id') ? $request->area_id : ($pendingDelivery->area_id ?? $delivery->area_id),
        'shift_id' => $request->filled('shift_id') ? $request->shift_id : ($pendingDelivery->shift_id ?? $delivery->shift_id),
        'type' => $request->filled('type') ? $request->type : ($pendingDelivery->type ?? $delivery->type),
        'has_vehicle' => $request->has('has_vehicle') ? $request->has_vehicle : ($pendingDelivery->has_vehicle ?? $delivery->has_vehicle),
        'vehicle_id' => $request->filled('vehicle_id') ? $request->vehicle_id : ($pendingDelivery->vehicle_id ?? $delivery->vehicle_id),
        'vehicle_type' => $request->filled('vehicle_type') ? $request->vehicle_type : ($pendingDelivery->vehicle_type ?? $delivery->vehicle_type),
        'status' => 'pending',
    ];

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());

        $folder = "assets/delivery/images/";
        $path = public_path($folder);

        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        $file->move($path, $fileName);

        $data['image'] = "https://www.programshouse.com/swsw/public/" . $folder . $fileName;
    } else {
        $data['image'] = $pendingDelivery->image ?? $delivery->image;
    }

    PendingDelivery::updateOrCreate(
        ['delivery_user_id' => $delivery->id],
        $data
    );

    return response()->json([
        'status' => true,
        'message' => 'Waiting for admin approval.',
        'data' => null
    ]);
}

    public function forgetPassword(Request $request)
    {
        if ($user->role !== 'kitchen') {
            abort(404);
        }

        $code = rand(100000, 999999);

        $user->forceFill([
            'code' => $code,
            'code_expires_at' => now()->addMinutes(10),
        ])->save();

        return redirect()
            ->route('admin.kitchens.index')
            ->with('success', 'تم إنشاء كود تغيير كلمة المرور بنجاح')
            ->with('generated_code_user_id', $user->id)
            ->with('generated_code', $code);
    }


    public function resetPassword(Request $request)
    {
        // $delivery = $request->user();

        // $delivery->load([
        //     'area',
        //     'shift',
        // ]);

        // // آخر شفتات (لو عندك جدول shift logs)
        // $lastShifts = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
        //     ->latest()
        //     ->take(5)
        //     ->get();

        // $delivery_data = [new DeliveryResource($delivery)];

        // return response()->json([
        //     'status' => true,
        //     'data' => [
        //         'user' => $delivery_data,
        //         'last_shifts' => $lastShifts,
        //     ]
        // ]);
    }

    public function deliveryStatus($id)
    {
        $delivery = DeliveryUser::find($id);

        if (!$delivery) {
            return response()->json([
                'status' => false,
                'message' => 'Delivery user not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'delivery_status' => $delivery->status,
            'data' => $delivery,
        ]);
    }
}
