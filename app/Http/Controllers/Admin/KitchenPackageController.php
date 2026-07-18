<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\KitchenPackage;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\KitchenPackageResource;

class KitchenPackageController extends Controller
{
    public function index()
    {
        $packages = KitchenPackage::latest()->get();

        return view('admin.kitchen_packages.index', compact('packages'));
    }

    public function create()
    {
        return view('admin.kitchen_packages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $data['features'] = $request->features
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : [];

        $data['active'] = $request->has('active');

        KitchenPackage::create($data);

        return redirect()
            ->route('admin.kitchen-packages.index')
            ->with('success', 'تم إضافة الباقة بنجاح');
    }

    public function edit(KitchenPackage $kitchenPackage)
    {
        return view('admin.kitchen_packages.edit', compact('kitchenPackage'));
    }

    public function update(Request $request, KitchenPackage $kitchenPackage)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'desc' => 'nullable|string',
            'features' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'duration' => 'required|integer|min:1',
            'active' => 'nullable|boolean',
        ]);

        $data['features'] = $request->features
            ? array_values(array_filter(array_map('trim', explode("\n", $request->features))))
            : [];

        $data['active'] = $request->has('active');

        $kitchenPackage->update($data);

        return redirect()
            ->route('admin.kitchen-packages.index')
            ->with('success', 'تم تعديل الباقة بنجاح');
    }

    public function destroy(KitchenPackage $kitchenPackage)
    {
        $kitchenPackage->delete();

        return redirect()
            ->route('admin.kitchen-packages.index')
            ->with('success', 'تم حذف الباقة بنجاح');
    }






    public function packages(): JsonResponse
{
    $packages = KitchenPackage::where('active', true)
        ->latest()
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Packages fetched successfully.',
        'data' => KitchenPackageResource::collection($packages),
    ]);
}
}
