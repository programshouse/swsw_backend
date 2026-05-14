<?php

namespace App\Http\Controllers\rate;

use App\Http\Controllers\Controller;
use App\Models\Rate;
use Illuminate\Http\Request;

class RateController extends Controller
{

    // create rating
    function rate_kitchen(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'star' => 'required|min:1|max:5|integer',
            'note' => 'nullable|string'
        ]);

        Rate::create([
            'user_id' => $user->id,
            'role' =>  'kitchen',
            'kitchen_profile_id' => $validated['kitchen_profile_id'],
            'star' => $validated['star'],
            'note' => $validated['note']
        ]);

        return response()->json([
            'message' => 'rate added successfully'
        ], 203);
    }

    function rate_delivery(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'kitchen_profile_id' => 'required|exists:kitchen_profiles,id',
            'star' => 'required|min:1|max:5|integer',
            'note' => 'nullable|string'
        ]);

        Rate::create([
            'user_id' => $user->id,
            'role' =>  'delivery',
            'kitchen_profile_id' => $validated['kitchen_profile_id'],
            'star' => $validated['star'],
            'note' => $validated['note']
        ]);

        return response()->json([
            'message' => 'rate added successfully'
        ], 203);
    }

    // user rating

}
