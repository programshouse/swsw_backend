<?php

namespace App\Http\Controllers\client;

use App\Http\Controllers\Controller;
use App\Models\Carusel;
use App\Models\Area;
use App\Models\User;
use App\Helpers\FileHelper;
use Illuminate\Http\Request;
use App\Http\Resources\CaruselResource;

class CaruselController extends Controller
{
    public function index(Request $request)
{
    $user = $request->user();

    $carusels = Carusel::with('kitchenProfile')
        ->where(function ($query) use ($user) {
            $query->where('area_id', $user->area_id)
                ->orWhereNull('area_id');
        })
        ->get();

    return response()->json([
        'carusel' => CaruselResource::collection($carusels),
    ]);
}

    public function GetAll(Request $request)
    {
        $carusels = Carusel::with(['area', 'kitchen'])->latest()->get();

        $areas = Area::latest()->get();

        $kitchens = User::where('role', 'kitchen')
            ->orderBy('name')
            ->get();

        return view('admin.sliders.index', compact('carusels', 'areas', 'kitchens'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'area_id' => 'nullable|exists:areas,id',
            'kitchen_id' => 'nullable|exists:users,id',
        ]);

        $imagePath = FileHelper::uploadImage(
            $request,
            'image',
            'assets/uploads/carusel'
        );

        Carusel::create([
            'image' => $imagePath,
            'area_id' => $validated['area_id'] ?? null,
            'kitchen_id' => $validated['kitchen_id'] ?? null,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إضافة السلايدر بنجاح');
    }

    public function destroy(Request $request, Carusel $carusel)
    {
        FileHelper::deleteFile($carusel->image);

        $carusel->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف السلايدر بنجاح');
    }
}
