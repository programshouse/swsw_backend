<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RateStore;

class AdminRateController extends Controller
{
    public function create(User $kitchen)
    {
        if ($kitchen->role !== 'kitchen') {
            return redirect()
                ->route('admin.kitchens.index')
                ->with('error', 'This user is not a kitchen.');
        }

        return view('admin.kitchens.rate', compact('kitchen'));
    }

    public function rateKitchenAsClient(Request $request, User $kitchen)
    {
        if ($kitchen->role !== 'kitchen') {
            return back()->with('error', 'This user is not a kitchen.');
        }

        $validated = $request->validate([
            'order_id' => 'nullable|exists:orders,id',
            'score' => 'required|integer|min:1|max:5',
            'details' => 'nullable|string',
        ]);

        RateStore::create([
            'order_id' => $validated['order_id'] ?? null,
            'delivery_user_id' => null,
            'user_id' => $kitchen->id,
            'user_rate_id' => null,
            'rater_type' => 'client',
            'rated_type' => 'kitchen',
            'score' => $validated['score'],
            'details' => $validated['details'] ?? null,
        ]);

        return redirect()
            ->route('admin.kitchens.index')
            ->with('success', 'تم إضافة التقييم بنجاح');
    }


    public function kitchenRates(User $kitchen)
{
    if ($kitchen->role !== 'kitchen') {
        return redirect()
            ->route('admin.kitchens.index')
            ->with('error', 'This user is not a kitchen.');
    }

    $rates = RateStore::query()
        ->where('rated_type', 'kitchen')
        ->where('user_id', $kitchen->id)
        ->latest()
        ->get();

    $average = round($rates->avg('score'), 1);
    $total = $rates->count();

    return view('admin.kitchens.rates', compact('kitchen', 'rates', 'average', 'total'));
}
}