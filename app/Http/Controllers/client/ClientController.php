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

class ClientController extends Controller
{
    public function ClientHome(Request $request)
    {
        $user = $request->user()->id;
        // default user address
        $default_address = UserAddress::where('user_id', $user)->where('is_default', true)->first();

        $kitchens_in_area = KitchenProfile::where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->where('statue', 'approved')->with('government', 'area')->get();

        $carusel = Carusel::all();

        // $kitchens_in_area->load('government' , 'area');

        return response()->json([
            'kitchens' => ClientKitchenResource::collection($kitchens_in_area),
            'carusel' => CaruselResource::collection($carusel)
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

        $meals_in_cat = Meal::where('category_id', $category->id)->whereHas('kitchen', function ($k) use ($default_address) {
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

        $meals_in_cat = Meal::whereLike('name', '%'. $search . '%')->whereHas('kitchen', function ($k) use ($default_address) {
            return $k->where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->where('statue', 'approved');
        })->get();


        return response()->json([
            'data' => ClientMealResource::collection($meals_in_cat)
        ] , 200);
    }

    public function client_my_profile(Request $request) {

        $user = $request->user() ;

        if($user->role !== 'client'){
            return response()->json([
                'message' => 'you are not a client '
            ] , 403);
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

        $kitchens = KitchenProfile::where('statue' , 'approved')->where('government_id', $default_address->government_id)->where('area_id', $default_address->area_id)->whereLike('name' ,  '%'. $search . '%')->get() ;


        return response()->json([
            'data' => KitchenProfileResource::collection($kitchens)
        ] , 200);
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
    $clients = User::where('role', 'client')
        ->latest()
        ->get();

    return view('admin.clients.index', compact('clients'));
}

public function client_profile(Request $request, User $user)
{
    if ($user->role !== 'client') {
        abort(404);
    }

    $user->load(['address', 'orders']);

    return view('admin.clients.show', compact('user'));
}
}
