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
            'name' => 'required|string|max:255',
            'max_km' => 'required|integer|min:0',
            'zone' => 'nullable|string|max:255',
        ]);

        Vehicle::create($data);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle created successfully');
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

    public function update(Request $request, $id)
    {
        $vehicle = Vehicle::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'max_km' => 'required|integer|min:0',
            'zone' => 'nullable|string|max:255',
        ]);

        $vehicle->update($data);

        return redirect()
            ->route('admin.vehicles.index')
            ->with('success', 'Vehicle updated successfully');
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
