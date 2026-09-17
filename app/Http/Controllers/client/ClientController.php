<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientKitchenResource;
use App\Http\Resources\ClientMealResource;
use App\Http\Resources\KitchenProfileResource;
use App\Models\Category;
use App\Models\KitchenProfile;
use App\Models\Meal;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Http\Request;
use App\Models\Carusel;
use App\Http\Resources\CaruselResource;
use App\Http\Resources\ClientProfileResource;
use App\Http\Resources\DashboardClientREsource;
use App\Models\DeliveryUser;
use App\services\PointService;
use App\Models\Point;



class ClientController extends Controller
{
    public function ClientHome(Request $request)
    {
        $user = $request->user()->id;

        // default user address
        $default_address = UserAddress::where('user_id', $user)
            ->where('is_default', true)
            ->first();

        $kitchens_in_area = KitchenProfile::query()
            ->where('government_id', $default_address->government_id)
            ->where('area_id', $default_address->area_id)
            ->where('statue', 'approved')
            ->whereHas('user', function ($query) {
                $query->where('is_company', 0);
            })
            ->with([
                'government',
                'area',
                'user',

                'meals' => function ($query) {
                    $query->where('approved', 'approved');
                },

                // MealResource يستخدم العلاقات دي
                'meals.kitchen',
                'meals.category',
            ])
            ->get();

        $carusel = Carusel::all();

        return response()->json([
            'kitchens' => ClientKitchenResource::collection(
                $kitchens_in_area
            ),
            'carusel' => CaruselResource::collection(
                $carusel
            ),
        ]);
    }

    public function kitchen_details(Request $request, KitchenProfile $kitchen)
    {

        // check if kitchen is approved
        if ($kitchen->statue !== 'approved') {
            return response()->json([
                "message" => 'المطبخ ده مش متاح حاليا'
            ], 403);
        }


        $user = $request->user();

        // default user address
        $default_address = UserAddress::where('user_id', $user->id)->where('is_default', true)->first();


        // return $default_address->government_id ;

        // check if client and kitchen in same area
        if ($default_address->government_id !== $kitchen->government_id && $default_address->area_id !== $kitchen->area_id) {

            return response()->json([
                'message' => 'this kitchen is not in your area'
            ], 405);
        }

        // check if kitchen is approved
        if ($kitchen->statue !== 'approved') {
            return response()->json([
                'message' => 'this kitchen is not available right now  '
            ], 405);
        }

        $kitchen->load('work_day', 'meals', 'government', 'area');

        return response()->json([
            'kitchen_data' => new ClientKitchenResource($kitchen)
        ], status: 200);
    }

    public function Meals(Request $request, Category $category)
    {

        $user = $request->user()->id;
        // default user address
        $default_address = UserAddress::where('user_id', $user)->where('is_default', true)->first();

        $meals_in_cat = Meal::where('category_id', $category->id)->where('approved', 'approved')->whereHas('kitchen', function ($k) use ($default_address) {
            return $k->where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->where('statue', 'approved');
        })->get();


        return  ClientMealResource::collection($meals_in_cat);
    }

    public function meals_list(Request $request)
    {

        $search = $request->query('search');

        $user = $request->user()->id;
        // default user address
        $default_address = UserAddress::where('user_id', $user)->where('is_default', true)->first();

        $meals_in_cat = Meal::whereLike('name', '%' . $search . '%')->whereHas('kitchen', function ($k) use ($default_address) {
            return $k->where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->where('statue', 'approved');
        })->get();


        return response()->json([
            'data' => ClientMealResource::collection($meals_in_cat)
        ], 200);
    }

    public function client_my_profile(Request $request)
    {

        $user = $request->user();

        if ($user->role !== 'client') {
            return response()->json([
                'message' => 'you are not a client '
            ], 403);
        }

        $user->load('address');

        return response()->json([
            'profile' => new ClientProfileResource($user)
        ], 200);
    }


    public function kitchens_list(Request $request)
    {

        $search = $request->query('search');

        $user = $request->user()->id;
        // default user address
        $default_address = UserAddress::where('user_id', $user)->where('is_default', true)->first();

        $kitchens = KitchenProfile::where('statue', 'approved')->where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->whereLike('name',  '%' . $search . '%')->get();


        return response()->json([
            'data' => KitchenProfileResource::collection($kitchens)
        ], 200);
    }

    // dashboard functions

    // function all_clients(Request $request) {
    //     $clients = User::where('role' , 'client')->get() ;

    //     return response()->json([
    //         'clients' => DashboardClientREsource::collection($clients)
    //     ] , 200) ;
    // }

    // function client_profile(Request $request , User $user) {
    //     return response()->json([
    //         'clients' => new DashboardClientREsource($user)
    //     ] , 200) ;
    // }



    public function all_clients(Request $request)
    {
        $search = $request->input('search');

        $clients = User::query()
            ->where('role', 'client')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->withSum(
                'pointTransactions as total_points',
                'points'
            )
            ->latest()
            ->get()
            ->map(function ($client) {

                if (empty($client->code)) {
                    $client->referrals_count = 0;
                    $client->total_points = (int) ($client->total_points ?? 0);

                    return $client;
                }

                $usersCount = User::query()
                    ->whereNotNull('referral_code')
                    ->where('referral_code', $client->code)
                    ->count();

                $deliveryCount = DeliveryUser::query()
                    ->whereNotNull('referral_code')
                    ->where('referral_code', $client->code)
                    ->count();

                $client->referrals_count = $usersCount + $deliveryCount;
                $client->total_points = (int) ($client->total_points ?? 0);

                return $client;
            });

        $points = Point::query()
            ->orderBy('number')
            ->get();

        return view('admin.clients.index', compact(
            'clients',
            'points'
        ));
    }



    public function client_profile(Request $request, User $user)
    {
        if ($user->role !== 'client') {
            abort(404);
        }

        $user->load([
            'address.area',
            'address.government',
        ]);

        $orders = $user->orders()
            ->when($request->filled('id'), function ($query) use ($request) {
                $query->where('id', 'like', '%' . $request->id . '%');
            })
            ->latest()
            ->get();

        $user->setRelation('orders', $orders);

        return view('admin.clients.show', compact('user'));
    }




    public function kitchensByArea(Request $request)
    {
        $validated = $request->validate([
            'area_id' => 'required|exists:areas,id',
        ]);

        $kitchens = KitchenProfile::with('user')
            ->where('statue', 'approved')
            ->where('area_id', $validated['area_id'])
            ->get();

        return response()->json([
            'status' => true,
            'data' => ClientKitchenResource::collection($kitchens),
        ]);
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

        $client = User::query()
            ->where('role', 'client')
            ->findOrFail($id);

        $point = Point::findOrFail($data['point_id']);

        $pointsNumber = (int) $point->number;

        if ($pointsNumber <= 0) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'عدد النقاط يجب أن يكون أكبر من صفر');
        }

        $pointService->add(
            owner: $client,
            points: $pointsNumber,
            source: 'admin_add',
            reference: $point,
            notes: $data['notes']
                ?? 'تمت إضافة النقاط للعميل بواسطة الأدمن'
        );

        return redirect()
            ->route('admin.clients.index')
            ->with(
                'success',
                "تمت إضافة {$pointsNumber} نقطة إلى {$client->name} بنجاح"
            );
    }





    public function companyKitchens(Request $request)
    {
        $user = $request->user();



        $kitchens = KitchenProfile::query()
            ->where('statue', 'approved')
            ->whereHas('user', function ($query) {
                $query->where('is_company', 1);
            })
            ->with([
                'government',
                'area',
                'user',
            ])
            ->get();

        return response()->json([
            'status' => true,
            'data' => KitchenProfileResource::collection($kitchens),
        ]);
    }
}
