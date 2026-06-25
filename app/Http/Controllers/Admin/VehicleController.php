<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicle;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::latest()->paginate(10);

        return view('admin.vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        return view('admin.vehicles.create');
    }

  public function store(Request $request)
{
    $data = $request->validate([
        'name_en' => 'required|string|max:255',
        'name_ar' => 'required|string|max:255',
        'max_km' => 'required|integer|min:0',
        'price_distance_meters' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'estimated_time_minutes' => 'required|integer|min:0',
    ]);

    Vehicle::create($data);

    return redirect()
        ->route('admin.vehicles.index')
        ->with('success', 'تم إنشاء الوسيلة بنجاح');
}

public function update(Request $request, $id)
{
    $vehicle = Vehicle::findOrFail($id);

    $data = $request->validate([
        'name_en' => 'required|string|max:255',
        'name_ar' => 'required|string|max:255',
        'max_km' => 'required|integer|min:0',
        'price_distance_meters' => 'required|integer|min:0',
        'price' => 'required|numeric|min:0',
        'estimated_time_minutes' => 'required|integer|min:0',
    ]);

    $vehicle->update($data);

    return redirect()
        ->route('admin.vehicles.index')
        ->with('success', 'تم تعديل الوسيلة بنجاح');
}

    public function show($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return view('admin.vehicles.show', compact('vehicle'));
    }

    public function edit($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        return view('admin.vehicles.edit', compact('vehicle'));
    }

  

    public function destroy($id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $vehicle->delete();

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle deleted successfully');
    }
}
