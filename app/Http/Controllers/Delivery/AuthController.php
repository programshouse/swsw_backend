<?php

namespace App\Http\Controllers\Delivery;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DeliveryUser;
use App\Models\DeliveryShiftLog;
use App\Http\Resources\DeliveryResource;
use App\Models\PendingDelivery;
use Illuminate\Support\Facades\Hash;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:delivery_users,email',
            'phone' => 'required|unique:delivery_users,phone',
            'birthdate' => 'required|date',
            'password' => 'required|min:6|confirmed',

            'government_id' => 'required|string|exists:governments,id',

            'level_id' => 'required|string|exists:levels,id',

            'area_id' => 'required|exists:areas,id',

            'shift_id' => 'required|exists:shifts,id',

            'type' => 'required|in:company,freelance',

            'has_vehicle' => 'required|boolean',
            'vehicle_id' => 'nullable|string|exists:vehicles,id',
            'vehicle_type' => 'nullable|in:car,motorcycle,bicycle',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',


        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('delivery_users', 'public');
        }

        $delivery = DeliveryUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'birthdate' => $request->birthdate,
            'password' => bcrypt($request->password),
            'government_id' => $request->government_id,
            'level_id' => $request->level_id,
            'area_id' => $request->area_id,
            'shift_id' => $request->shift_id,
            'type' => $request->type,
            'has_vehicle' => $request->has_vehicle,
            'vehicle_id' => $request->vehicle_id,
            'vehicle_type' => $request->vehicle_type,
            'image' => $imagePath,
            'status' => 'pending'
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
        ]);

        // آخر شفتات (لو عندك جدول shift logs)
        $lastShifts = DeliveryShiftLog::where('delivery_user_id', $delivery->id)
            ->latest()
            ->take(5)
            ->get();

        $delivery_data = [new DeliveryResource($delivery)];

        return response()->json([
            'status' => true,
            'data' => [
                'user' => $delivery_data,
                'last_shifts' => $lastShifts,
            ]
        ]);
    }


    public function updateProfile(Request $request)
    {
        $delivery = $request->user();

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:delivery_users,email,' . $delivery->id,
            'phone' => 'sometimes|unique:delivery_users,phone,' . $delivery->id,
            'birthdate' => 'sometimes|date',

            'government_id' => 'sometimes|string',

            'area_id' => 'sometimes|exists:areas,id',
            'shift_id' => 'sometimes|exists:shifts,id',

            'type' => 'sometimes|in:company,freelance',

            'has_vehicle' => 'sometimes|boolean',
            'vehicle_type' => 'nullable|string',

            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);


        // image update
        if ($request->hasFile('image')) {
            $image_path = $request->file('image')->store('delivery_users', 'public');
        } else {
            $image_path = $delivery->image;
        }

        $pending_delivery = PendingDelivery::where('delivery_user_id', $delivery->id)->first();

        if ($pending_delivery) {

            $pending_delivery->update([
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birthdate' => $request->birthdate,
                'government_id' => $request->government_id,
                'area_id' => $request->area_id,
                'shift_id' => $request->shift_id,
                'type' => $request->type,
                'has_vehicle' => $request->has_vehicle,
                'vehicle_id' => $request->vehicle_id,
                'vehicle_type' => $request->vehicle_type,
                'image' => $image_path,
                'status' => 'pending'
            ]);
        } else {

            PendingDelivery::create([
                'delivery_user_id' => $delivery->id,
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
                'birthdate' => $request->birthdate,
                'government_id' => $request->government_id,
                'area_id' => $request->area_id,
                'shift_id' => $request->shift_id,
                'type' => $request->type,
                'has_vehicle' => $request->has_vehicle,
                'vehicle_id' => $request->vehicle_id,
                'vehicle_type' => $request->vehicle_type,
                'image' => $image_path,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Waiting for admin approval .',
            'data' => null
        ]);
    }
}
