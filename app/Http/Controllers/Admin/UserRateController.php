<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRate;
use Illuminate\Http\Request;
use App\Models\RateStore;


class UserRateController extends Controller
{

      public function index(Request $request)
      {
            $types = UserRate::select('type')
                  ->distinct()
                  ->pluck('type');

            $rates = UserRate::when($request->type, function ($q) use ($request) {
                  $q->where('type', $request->type);
            })->get();

            return view('admin.rates.index', compact('rates', 'types'));
      }

      public function create()
      {
            $types = UserRate::select('type')->get();
            return view('admin.rates.create', compact('types'));
      }

      public function store(Request $request)
      {
            $request->validate([
                  'name_ar' => 'required|string|max:255',
                  'name_en' => 'required|string|max:255',
                  'type' => 'required|string|in:delivery,client,kitchen',
                  'target_type' => 'required|string|in:delivery,client,kitchen|different:type',
                  'max_score' => 'required|integer|in:1,2,3,4,5',
            ]);

            UserRate::create([
                  'name_ar' => $request->name_ar,
                  'name_en' => $request->name_en,
                  'type' => $request->type,
                  'target_type' => $request->target_type,
                  'max_score' => $request->max_score,
            ]);

            return redirect()
                  ->route('admin.rates.index')
                  ->with('success', 'Rate Created Successfully');
      }

      public function edit(UserRate $rate)
      {
            return view('admin.rates.edit', compact('rate'));
      }

      public function update(Request $request, UserRate $rate)
      {

            $request->validate([
                  'name_ar' => 'required|string|max:255',
                  'name_en' => 'required|string|max:255',
                  'type' => 'required|string|in:delivery,client,kitchen',
                  'target_type' => 'required|string|in:delivery,client,kitchen|different:type',
                  'max_score' => 'required|integer|in:1,2,3,4,5',
            ]);

            $rate->update([
                  'name_ar' => $request->name_ar,
                  'name_en' => $request->name_en,
                  'type' => $request->type,
                  'target_type' => $request->target_type,
                  'max_score' => $request->max_score,
            ]);

            return redirect()
                  ->route('admin.rates.index')
                  ->with('success', 'Rate Updated Successfully');
      }


      public function destroy(RateStore $rate)
      {
            $rate->delete();

            return back()->with('success', 'تم حذف التقييم بنجاح.');
      }
}
