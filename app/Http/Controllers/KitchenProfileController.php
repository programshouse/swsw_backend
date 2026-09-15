<?php

namespace App\Http\Controllers;

use App\Http\Resources\DashboardKitchenProfileResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\KitchenProfile;
use App\Http\Resources\MealResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\KitchenProfileResource;
use App\Models\Wallet;
use App\Models\UserAddress;
use Illuminate\Support\Facades\DB;
use App\Models\DeliveryUser;
use App\Helpers\FileHelper;
use Illuminate\Http\JsonResponse;


class KitchenProfileController extends Controller
{
    public function store(Request $request)
    {

        // check if user account is active or not
        if ($request->user()->cannot('create', KitchenProfile::class)) {
            return response()->json([
                'message' => "your profile is not active yet please contact support to active it.",
            ], 401);
        }

        $user = $request->user();

        // validation

        $validated = $request->validate([
            'work_day_id' => 'required|exists:work_days,id',
            'phone' => 'required|string|max:255|unique:kitchen_profiles,phone,except,id',
            'whatsapp' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'working_time_start' => 'required',
            'working_time_end' => 'required',
            'have_delivery' => 'required',
            'name' => 'required|string|max:255',
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        $logo_path = FileHelper::uploadImage(
            $request,
            'logo',
            'assets/uploads/kitchens'
        );

        $cover_path = FileHelper::uploadImage(
            $request,
            'cover',
            'assets/uploads/kitchens'
        );


        $profile = KitchenProfile::create([
            'user_id' => $user->id,
            'work_day_id' => $validated['work_day_id'],
            'name' => $validated['name'],
            'phone' => $validated['phone'],
            'whatsapp' => $validated['whatsapp'] ?? null,
            'facebook' => $validated['facebook'] ?? null,
            'location' => $validated['location'] ?? null,
            'working_time_start' => $validated['working_time_start'],
            'working_time_end' => $validated['working_time_end'],
            'have_delivery' => $validated['have_delivery'],
            'logo' => $logo_path,
            'cover' => $cover_path,
            'government_id' =>  $user->government_id,
            'area_id' =>  $user->area_id
        ]);



        // $profile->load('user', 'work_day');

        // create wallet for this kitchen user
        Wallet::create([
            'owner_id' => $request->user()->id,
            'owner_type' => 'kitchen'
        ]);

        return response()->json([
            'message' => "profile created successfully",
        ]);
    }

    public function show(Request $request, User $profile)
    {

        return new DashboardKitchenProfileResource($profile->profile);
    }

    public function me(Request $request)
    {

        $user = $request->user();

        $profile = KitchenProfile::where('user_id', '=', $user->id)->first();

        if (!$profile) {
            return response()->json([
                'message' => 'Profile not found',
            ], 404);
        }
        $defaultAddress = UserAddress::with(['government', 'area'])
            ->where('user_id', $user->id)
            ->where('is_default', 1)
            ->first();

        return new KitchenProfileResource($profile);
    }

    // public function active(Request $request, KitchenProfile $profile)
    // {

    //     $validated = $request->validate([
    //         'statue' => 'required|in:approved,rejected'
    //     ]);

    //     if ($validated['statue'] === $profile->statue) {
    //         return response()->json([
    //             'message' => "profile is already " . $validated['statue'],
    //         ], 400);
    //     }

    //     $profile->update([
    //         'statue' => $validated['statue']
    //     ]);

    //     return response()->json([
    //         'message' => $validated['statue'] === "approved" ? "account approved" : "account rejected",
    //     ], 201);
    // }

    public function active(Request $request, KitchenProfile $profile)
    {
        $validated = $request->validate([
            'statue' => 'required|in:approved,rejected'
        ]);

        if ($validated['statue'] === $profile->statue) {
            return redirect()
                ->back()
                ->with('error', 'الحالة مضافة بالفعل');
        }

        $profile->update([
            'statue' => $validated['statue']
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                $validated['statue'] === 'approved'
                    ? 'تم قبول المطبخ بنجاح'
                    : 'تم رفض المطبخ'
            );
    }

    // public function meals(Request $request, KitchenProfile $kitchen)
    // {

    //     return MealResource::collection($kitchen->meals);
    // }

    public function open_status(Request $request, KitchenProfile $kitchen)
    {
        $validated = $request->validate([
            'open_status' => 'required|in:open,closed,busy'
        ]);

        $kitchenProfile = $request->user()->profile();

        if (
            $kitchenProfile->open_status == 'closed'
            &&
            $kitchenProfile->close_reason == 'package_limit'
            &&
            $validated['open_status'] == 'open'
        ) {

            return response()->json([
                'message' => 'Please renew your package first.'
            ], 422);
        }

        $kitchen->update([
            'open_status' => 'closed',
            'close_reason' => 'package_limit'
        ]);

        return response()->json([
            'message' => "open status updated successfully",
            'open_status' => $validated['open_status']
        ], 201);
    }

    // public function add_star(Request $request, KitchenProfile $kitchen)
    // {
    //     $validated = $request->validate([
    //         'have_star' => 'required|boolean'
    //     ]);

    //     if ($validated['have_star'] === $kitchen->have_star) {
    //         return response()->json([
    //             'message' => "have star is already " . (boolval($validated['have_star']) ? 'added' : 'removed'),
    //         ], 400);
    //     }

    //     $kitchen->update([
    //         'have_star' => $validated['have_star']
    //     ]);

    //     return response()->json([
    //         'message' => "have star updated successfully",
    //         'have_star' => $validated['have_star']
    //     ], 201);
    // }

    public function add_star(Request $request, KitchenProfile $kitchen)
    {
        $validated = $request->validate([
            'have_star' => 'required|boolean'
        ]);

        $kitchen->update([
            'have_star' => $validated['have_star']
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                $validated['have_star']
                    ? 'تم إضافة النجمة للمطبخ'
                    : 'تم إزالة النجمة من المطبخ'
            );
    }


    public function kitchens(Request $request)
    {
        $kitchens = User::where('role', 'kitchen')
            ->with(['profile.government', 'profile.area'])
            ->latest()
            ->get()
            ->map(function ($kitchen) {

                if (empty($kitchen->code)) {
                    $kitchen->referrals_count = 0;
                    return $kitchen;
                }

                $usersCount = User::whereNotNull('referral_code')
                    ->where('referral_code', $kitchen->code)
                    ->count();

                $deliveryCount = DeliveryUser::whereNotNull('referral_code')
                    ->where('referral_code', $kitchen->code)
                    ->count();

                $kitchen->referrals_count = $usersCount + $deliveryCount;

                return $kitchen;
            });

        return view('admin.kitchens.index', compact('kitchens'));
    }

    public function kitchen_show(Request $request, User $profile)
    {
        if ($profile->role !== 'kitchen') {
            abort(404);
        }

        $profile->load([
            'profile.government',
            'profile.area',
            'profile.meals.category',
            'profile.orders',
            'profile.work_day',
        ]);

        return view('admin.kitchens.show', [
            'user' => $profile,
            'kitchen' => $profile->profile,
        ]);
    }

    public function generate_kitchen_user_forget_password_code(Request $request, User $user)
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

    // public function meals(Request $request, KitchenProfile $kitchen)
    // {
    //     $meals = $kitchen->meals()
    //         ->with(['category', 'kitchen'])
    //         ->latest()
    //         ->get();

    //     return view('admin.kitchens.meals', compact('kitchen', 'meals'));
    // }

    public function meals(Request $request, KitchenProfile $kitchen)
    {
        $kitchen->load([
            'user',
            'government',
            'area',
        ]);

        $meals = $kitchen->meals()
            ->with(['category', 'kitchen'])
            ->latest()
            ->get();

        return view('admin.kitchens.meals', [
            'kitchen' => $kitchen,
            'meals' => $meals,
        ]);
    }





    public function myPoints(Request $request): JsonResponse
    {
        $kitchen = $request->user();

        if (!$kitchen || $kitchen->role !== 'kitchen') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $perPage = (int) $request->get('per_page', 15);

        if ($perPage < 1) {
            $perPage = 15;
        }

        if ($perPage > 100) {
            $perPage = 100;
        }

        $transactionsQuery = $kitchen
            ->pointTransactions()
            ->latest('id');

        $currentBalance = (int) $kitchen
            ->pointTransactions()
            ->sum('points');

        $totalAdded = (int) $kitchen
            ->pointTransactions()
            ->where('points', '>', 0)
            ->sum('points');

        $totalDeducted = abs(
            (int) $kitchen
                ->pointTransactions()
                ->where('points', '<', 0)
                ->sum('points')
        );

        $transactions = $transactionsQuery
            ->paginate($perPage);

        $transactions->getCollection()->transform(function ($transaction) {
            return [
                'id' => $transaction->id,

                'type' => $transaction->points > 0
                    ? 'added'
                    : 'deducted',

                'points' => abs((int) $transaction->points),

                'signed_points' => (int) $transaction->points,

                'source' => $transaction->source,

                'notes' => $transaction->notes,

                'created_at' => optional(
                    $transaction->created_at
                )->format('Y-m-d H:i:s'),

                'created_at_human' => optional(
                    $transaction->created_at
                )->diffForHumans(),
            ];
        });

        return response()->json([
            'status' => true,
            'message' => 'Points retrieved successfully',

            'data' => [
                'summary' => [
                    'current_balance' => $currentBalance,
                    'total_added' => $totalAdded,
                    'total_deducted' => $totalDeducted,
                    'transactions_count' => $transactions->total(),
                ],

                'transactions' => $transactions->items(),

                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'per_page' => $transactions->perPage(),
                    'total' => $transactions->total(),
                    'from' => $transactions->firstItem(),
                    'to' => $transactions->lastItem(),
                    'has_more_pages' => $transactions->hasMorePages(),
                ],
            ],
        ]);
    }
}
