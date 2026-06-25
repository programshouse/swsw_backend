<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Carusel;
use App\Models\Area;
use Illuminate\Http\Request;
use App\Http\Resources\CaruselResource;
use Illuminate\Support\Facades\Storage;

class CaruselController extends Controller
{

  public function index(Request $request)
{
    $user = $request->user();

    $carusels = Carusel::where('area_id', $user->area_id)
        ->orWhereNull('area_id')
        ->get();

    return response()->json([
        'carusel' => CaruselResource::collection($carusels),
    ], 200);
}

    // public function store(Request $request) {
    //     $validated = $request->validate([
    //         'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
    //     ]);

    //     $logo_path = $request->file('image')->store('carusel', 'public');

    //     $carusel = Carusel::create([
    //         'image' => $logo_path,
    //     ]);

    //     return response()->json([
    //         'message' => 'Carusel created successfully',
    //     ], 201);
    // }

    // public function destroy(Request $request, Carusel $carusel) {
    //     $carusel->delete();
    //     return response()->json([
    //         'message' => 'Carusel deleted successfully',
    //     ], 200);
    // }

    public function GetAll(Request $request)
    {
        $carusels = Carusel::with('area')->latest()->get();
        $areas = Area::latest()->get();

        return view('admin.sliders.index', compact('carusels', 'areas'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
              'area_id' => 'nullable|exists:areas,id',
        ]);

        $imagePath = $request->file('image')->store('carusel', 'public');

        Carusel::create([
            'image' => $imagePath,
             'area_id' => $request->area_id,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إضافة السلايدر بنجاح');
    }

    public function destroy(Request $request, Carusel $carusel)
    {
        if ($carusel->image && Storage::disk('public')->exists($carusel->image)) {
            Storage::disk('public')->delete($carusel->image);
        }

        $carusel->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف السلايدر بنجاح');
    }
}
