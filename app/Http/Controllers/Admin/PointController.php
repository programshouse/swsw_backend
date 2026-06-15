<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Point;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function index()
    {
        $points = Point::get();
        return view('admin.points.index', compact('points'));
    }

      public function create()
      {
            // $point = Point::select('number')->get();
            return view('admin.points.create');
      }

      public function store(Request $request)
      {
            $request->validate([
                  'name' => 'required|string|max:255',
                  'number' => 'required|integer',
                  'amount' => 'required|integer',
            ]);

            Point::create([
                  'name' => $request->name,
                  'number' => $request->number,
                  'amount' => $request->amount,
            ]);

            return redirect()
                  ->route('admin.points.index')
                  ->with('success', 'point Created Successfully');
      }

      public function edit(Point $point)
      {
            return view('admin.points.edit', compact('point'));
      }

      public function update(Request $request, Point $point)
      {

            $request->validate([
                 'name' => 'required|string|max:255',
                  'number' => 'required|integer',
                  'amount' => 'required|integer',
            ]);

            $point->update([
                  'name' => $request->name,
                  'number' => $request->number,
                  'amount' => $request->amount,
            ]);

            return redirect()
                  ->route('admin.points.index')
                  ->with('success', 'point Updated Successfully');
      }


      public function destroy(Point $point)
      {
            $point->delete();

            return redirect()
                  ->route('admin.points.index')
                  ->with('success', 'point Deleted Successfully');
      }

}
