<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UserRate;
use Illuminate\Http\Request;

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
                  'name' => 'required|string|max:255',
                  'type' => 'required|string|in:delivery,client,kitchen',
                  'max_score' => 'required|integer|in:1,2,3,4',
            ]);

            UserRate::create([
                  'name' => $request->name,
                  'type' => $request->type,
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
                  'name' => 'required|string|max:255',
                  'type' => 'required|string|in:delivery,client,kitchen',
                  'max_score' => 'required|integer|in:1,2,3,4',
            ]);

            $rate->update([
                  'name' => $request->name,
                  'type' => $request->type,
                  'max_score' => $request->max_score,
            ]);

            return redirect()
                  ->route('admin.rates.index')
                  ->with('success', 'Rate Updated Successfully');
      }


      public function destroy(UserRate $rate)
      {
            $rate->delete();

            return redirect()
                  ->route('admin.rates.index')
                  ->with('success', 'Rate Deleted Successfully');
      }
}
