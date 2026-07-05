<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meal;
use App\Models\MealOffer;
use App\Models\KitchenProfile;

class MealOfferController extends Controller
{
      public function index()
    {
        $offers = MealOffer::with(['meal', 'kitchen'])
            ->latest()
            ->paginate(20);

        return view('admin.meal_offers.index', compact('offers'));
    }

    public function create()
    {
        $kitchens = KitchenProfile::with('user')->latest()->get();
        $meals = Meal::latest()->get();

        return view('admin.meal_offers.create', compact('kitchens', 'meals'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'meal_id' => 'required|exists:meals,id',
            'percentage' => 'required|numeric|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status') ? 1 : 0;

        MealOffer::create($data);

        return redirect()
            ->route('admin.meal-offers.index')
            ->with('success', 'تم إضافة العرض بنجاح');
    }

    public function edit(MealOffer $mealOffer)
    {
        $kitchens = KitchenProfile::with('user')->latest()->get();
        $meals = Meal::latest()->get();

        return view('admin.meal_offers.edit', compact('mealOffer', 'kitchens', 'meals'));
    }

    public function update(Request $request, MealOffer $mealOffer)
    {
        $data = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'meal_id' => 'required|exists:meals,id',
            'percentage' => 'required|numeric|min:1|max:100',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->has('status') ? 1 : 0;

        $mealOffer->update($data);

        return redirect()
            ->route('admin.meal-offers.index')
            ->with('success', 'تم تعديل العرض بنجاح');
    }

    public function destroy(MealOffer $mealOffer)
    {
        $mealOffer->delete();

        return redirect()
            ->route('admin.meal-offers.index')
            ->with('success', 'تم حذف العرض بنجاح');
    }
}
