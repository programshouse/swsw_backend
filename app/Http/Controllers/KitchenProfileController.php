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
use App\services\KitchenPackageService;
use App\Models\KitchenPackageSubscription;
use App\Models\KitchenPackage;





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
            'cover' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'has_tax_record' => 'required|boolean',
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
            'area_id' =>  $user->area_id,
            'has_tax_record' => $validated['has_tax_record'],
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
        $kitchen = $profile->profile;

        if (!$kitchen) {
            return response()->json([
                'status' => false,
                'message' => 'Kitchen profile not found',
            ], 404);
        }

        $kitchen->load([
            'work_day',
            'meals',
            'government',
            'area',
            'user',
            'orders',

            'currentPackageSubscription.package',
        ]);

        return new DashboardKitchenProfileResource(
            $kitchen
        );
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
    public function open_status(Request $request)
    {
        $validated = $request->validate([
            'open_status' => 'required|in:open,closed,busy'
        ]);


        $kitchenProfile = $request->user()->profile;


        if (!$kitchenProfile) {
            return response()->json([
                'message' => 'Kitchen profile not found'
            ], 404);
        }


        // لو يحاول يفتح المطبخ نتحقق من الباقة
        if ($validated['open_status'] === 'open') {


            $packageCheck = app(KitchenPackageService::class)
                ->checkOrderLimit($kitchenProfile);


            if (!$packageCheck['allowed']) {


                $kitchenProfile->update([
                    'open_status' => 'closed',
                    'close_reason' => 'package_limit'
                ]);


                return response()->json([
                    'message' => 'Please renew your package first.'
                ], 422);
            }
        }



        $kitchenProfile->update([

            'open_status' => $validated['open_status'],

            // عند الفتح نمسح سبب الإغلاق الخاص بالباقة فقط
            'close_reason' =>
            $validated['open_status'] === 'open'
                ? null
                : $kitchenProfile->close_reason
        ]);



        return response()->json([
            'message' => 'open status updated successfully',
            'open_status' => $validated['open_status']
        ], 200);
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
        $governments = \App\Models\Government::query()
            ->orderBy('name_ar')
            ->get();

        $areas = \App\Models\Area::query()
            ->orderBy('name_ar')
            ->get();

        $packageNames = \App\Models\KitchenPackageSubscription::query()
            ->select('package_name')
            ->whereNotNull('package_name')
            ->distinct()
            ->orderBy('package_name')
            ->pluck('package_name');

        $query = User::where('role', 'kitchen')
            ->with([
                'profile.government',
                'profile.area',
                'profile.packageSubscriptions',
            ]);

        /*
    |--------------------------------------------------------------------------
    | Text search: name / email / phone / code
    |--------------------------------------------------------------------------
    */

        if ($request->filled('search')) {
            $search = $request->get('search');

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Account status
    |--------------------------------------------------------------------------
    */

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        /*
    |--------------------------------------------------------------------------
    | Kitchen status (profile->statue)
    |--------------------------------------------------------------------------
    */

        if ($request->filled('kitchen_status')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('statue', $request->get('kitchen_status'));
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Tax record
    |--------------------------------------------------------------------------
    */

        if ($request->filled('has_tax_record')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where(
                    'has_tax_record',
                    $request->get('has_tax_record') === '1'
                );
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Government
    |--------------------------------------------------------------------------
    */

        if ($request->filled('government_id')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('government_id', $request->get('government_id'));
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Area
    |--------------------------------------------------------------------------
    */

        if ($request->filled('area_id')) {
            $query->whereHas('profile', function ($q) use ($request) {
                $q->where('area_id', $request->get('area_id'));
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Package
    |--------------------------------------------------------------------------
    */

        if ($request->filled('package')) {
            if ($request->get('package') === 'none') {
                $query->whereDoesntHave(
                    'profile.packageSubscriptions',
                    function ($q) {
                        $q->where('status', 'active')
                            ->where('expires_at', '>=', now());
                    }
                );
            } else {
                $query->whereHas(
                    'profile.packageSubscriptions',
                    function ($q) use ($request) {
                        $q->where('status', 'active')
                            ->where('expires_at', '>=', now())
                            ->where(
                                'package_name',
                                $request->get('package')
                            );
                    }
                );
            }
        }

        $kitchens = $query
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

        return view('admin.kitchens.index', compact(
            'kitchens',
            'governments',
            'areas',
            'packageNames'
        ));
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

        $code = rand(1000000, 9999999);

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




    public function updateTaxRecord(Request $request, $id)
    {
        $kitchen = KitchenProfile::findOrFail($id);

        $kitchen->has_tax_record = $request->boolean('has_tax_record');
        $kitchen->save();

        return redirect()
            ->back()
            ->with('success', 'تم تحديث حالة السجل الضريبي بنجاح');
    }





    public function activatePackage(
        Request $request,
        $kitchenId
    ) {
        /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

        $validated = $request->validate([
            'kitchen_package_id' => [
                'required',
                'integer',
                'exists:kitchen_packages,id',
            ],
        ]);

        /*
    |--------------------------------------------------------------------------
    | Kitchen
    |--------------------------------------------------------------------------
    */

        $kitchen = KitchenProfile::findOrFail(
            $kitchenId
        );

        /*
    |--------------------------------------------------------------------------
    | Package
    |--------------------------------------------------------------------------
    |
    | لا نسمح بتفعيل باقة غير مفعلة من الإدارة.
    |
    */

        $package = KitchenPackage::query()
            ->where('id', $validated['kitchen_package_id'])
            ->where('active', 1)
            ->firstOrFail();

        DB::transaction(function () use (
            $kitchen,
            $package
        ) {

            /*
        |--------------------------------------------------------------------------
        | Expire old active subscriptions
        |--------------------------------------------------------------------------
        */

            KitchenPackageSubscription::query()
                ->where('kitchen_id', $kitchen->id)
                ->where('status', 'active')
                ->update([
                    'status' => 'expired',
                    'expires_at' => now(),
                    'expired_reason' =>
                    'replaced_by_admin',
                ]);

            /*
        |--------------------------------------------------------------------------
        | Calculate subscription dates
        |--------------------------------------------------------------------------
        */

            $startsAt = now();

            $expiresAt = match ($package->duration_unit) {
                'day' => $startsAt
                    ->copy()
                    ->addDays(
                        (int) $package->duration
                    ),

                'year' => $startsAt
                    ->copy()
                    ->addYears(
                        (int) $package->duration
                    ),

                default => $startsAt
                    ->copy()
                    ->addMonths(
                        (int) $package->duration
                    ),
            };

            /*
        |--------------------------------------------------------------------------
        | Create subscription
        |--------------------------------------------------------------------------
        */

            KitchenPackageSubscription::create([

                'user_id' =>
                $kitchen->user_id,

                'kitchen_id' =>
                $kitchen->id,

                'kitchen_package_id' =>
                $package->id,

                /*
             * Snapshot من بيانات الباقة وقت الاشتراك.
             */

                'package_name' =>
                $package->name,

                'package_price' =>
                $package->price,

                'package_duration' =>
                $package->duration,

                'duration_unit' =>
                $package->duration_unit,

                /*
             * Subscription status
             */

                'status' => 'active',

                'starts_at' =>
                $startsAt,

                'expires_at' =>
                $expiresAt,

                /*
             * بما إن الأدمن هو اللي فعلها يدويًا
             */

                'paid_at' =>
                now(),

                'payment_reference' =>
                'ADMIN-'
                    . $kitchen->id
                    . '-'
                    . now()->format('YmdHis'),

                'expired_reason' =>
                null,
            ]);
        });

        return back()->with(
            'success',
            'تم تفعيل الباقة للمطبخ بنجاح'
        );
    }





    public function expirePackage(
        Request $request,
        $kitchenId
    ) {
        $kitchen = KitchenProfile::findOrFail(
            $kitchenId
        );

        $subscription =
            KitchenPackageSubscription::query()
            ->where('kitchen_id', $kitchen->id)
            ->where('status', 'active')
            ->latest('id')
            ->first();

        if (!$subscription) {
            return back()->with(
                'error',
                'لا توجد باقة فعالة لهذا المطبخ'
            );
        }

        $subscription->update([

            'status' => 'expired',

            'expires_at' => now(),

            'expired_reason' =>
            'expired_by_admin',
        ]);

        return back()->with(
            'success',
            'تم إنهاء باقة المطبخ بنجاح'
        );
    }
}
