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

        return view('admin.areas.index', compact('areas', 'governments'));
    }


    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:areas,name',
            'government_id' => 'required|exists:governments,id',
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',


        ]);

        $area = Area::create([
            'name' => trim($validated['name']),
            'government_id' => $validated['government_id'],
            'lat' => $validated['lat'],
            'lng' => $validated['lng'],
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

    public function updateLocation(Request $request, Area $area)
    {
        $validated = $request->validate([
            'lat' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'lng' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
            'polygon' => [
                'required',
                'json',
            ],
        ], [
            'polygon.required' => 'يجب رسم حدود المنطقة على الخريطة.',
            'polygon.json' => 'بيانات حدود المنطقة غير صحيحة.',
        ]);

        $polygon = json_decode($validated['polygon'], true);

        if (!is_array($polygon) || count($polygon) < 3) {
            return back()
                ->withErrors([
                    'polygon' => 'يجب تحديد ثلاث نقاط على الأقل لرسم المنطقة.',
                ])
                ->withInput();
        }

        $area->update([
            'lat' => $validated['lat'] ?? null,
            'lng' => $validated['lng'] ?? null,
            'polygon' => $polygon,
        ]);

        return back()->with(
            'success',
            'تم حفظ حدود المنطقة بنجاح.'
        );
    }
}
