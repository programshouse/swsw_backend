<?php

namespace App\Http\Controllers;

use App\Models\Meal;
use Illuminate\Http\Request;
use App\Models\KitchenProfile;
use App\Http\Resources\MealResource;
use Illuminate\Support\Facades\Storage;

class MealController extends Controller
{

    public function my_meals(Request $request)
    {
        $user = $request->user()->profile;
        $kitchen = KitchenProfile::find($user->id);

        return MealResource::collection($kitchen->meals);
    }

    public function store(Request $request)
    {

        if ($request->user()->cannot('create', Meal::class)) {
            return response()->json([
                'message' => "your kitchen profile is not active yet please contact support to active it",
            ], 401);
        }


        $user = $request->user()->profile;

        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'price' => 'required|numeric|min:1',
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'available_delivery_today' => 'required',
            'recipe' => 'required|string',
            'preparation_time' => 'required|integer|min:1',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('kitchens-meals', 'public');
        }

        $meal  = Meal::create([
            'category_id' => $validated['category_id'],
            'name' => $validated['name'],
            'description' => $validated['description'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'available_delivery_today' => $validated['available_delivery_today'],
            'recipe' => $validated['recipe'],
            'image' =>  $path,
            'kitchen_profile_id' => $user->id,
            'preparation_time' => $validated['preparation_time'],
        ]);

        return response()->json([
            'message' => "meal added successfully",
        ]);
    }

    // public function approveMeal(Request $request, Meal $meal)
    // {

    //     // validation
    //     $validated = $request->validate([
    //         'approve' => 'string|in:approved,rejected'
    //     ]);

    //     $message = $validated['approve'] === 'approved' ? 'meal approved successfully' : 'meal rejected';

    //     $meal->update([
    //         'approved' => $validated['approve']
    //     ]);

    //     return response()->json([
    //         'message' => $message
    //     ]);
    // }

    // public function allMeals(Request $request)
    // {
    //     $meals = Meal::all();
    //     return MealResource::collection($meals);
    // }


   public function allMeals(Request $request)
{
    $meals = Meal::with(['kitchen',  'category'])
        ->latest()
        ->get();

    return view('admin.meals.index', compact('meals'));
}

    public function approveMeal(Request $request, Meal $meal)
    {
        $validated = $request->validate([
            'approve' => 'required|string|in:approved,rejected'
        ]);

        $meal->update([
            'approved' => $validated['approve']
        ]);

        return redirect()
            ->back()
            ->with('success', $validated['approve'] === 'approved'
                ? 'تم قبول الوجبة بنجاح'
                : 'تم رفض الوجبة');
    }


    public function switchAvailability(Request $request,  Meal $meal)
    {

        $validate = $request->validate([
            'availability' => 'required|boolean'
        ]);

        $meal->update([
            'availability' => $validate['availability']
        ]);
        return response()->json([
            'message' => $validate['availability'] ? 'تم تفعيل الوجبه بنجاح' : 'تم الغاء تفعيل الوجبه بنجاح'
        ], 201);
    }


    public function destroy(Request $request, Meal $meal)
    {

        $meal->delete();

         return redirect()
        ->back()
        ->with('success', 'تم حذف الوجبة بنجاح');
    }
}
