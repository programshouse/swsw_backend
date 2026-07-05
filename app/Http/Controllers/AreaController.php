<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{

    public function appIndex()
{
    $areas = Area::with('government')->get()->map(function ($area) {
        return [
            'id' => $area->id,
            'name' => $area->name,
            'government' => [
                'id' => $area->government?->id,
                'name' => $area->government?->name,
            ],
        ];
    });

    return response()->json([
        'status' => true,
        'areas' => $areas,
    ], 200);
}



    public function index()
    {

        $areas = Area::get();
        $governments = $areas->load('government');

        return view('admin.areas.index', compact('areas','governments'));

        
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'government_id' => 'required|exists:governments,id',
        ]);

        $area = Area::create([
            'name' => trim($validated['name']),
            'government_id' => $validated['government_id']
        ]);

        $area->load('government');

        return response()->json([
            'message' => 'area created successfully',
            'area' => $area
        ], 201);
    }


    public function destroy(Request $request, Area $area)
    {

        $area->delete();

        return response()->json([
            'message' => 'area deleted successfully',
        ], 200);
    }


     public function toggleStatus(Request $request, Area $area)
    {
        $area->update([
            'is_active' => !$area->is_active
        ]);

        return redirect()
            ->back()
            ->with(
                'success',
                $area->is_active
                    ? 'تم تفعيل المحافظة'
                    : 'تم تعطيل المحافظة'
            );
    }
}
