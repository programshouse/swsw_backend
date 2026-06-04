<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Level;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $levels = Level::latest()->paginate(10);

        return view('admin.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('admin.levels.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cash_money' => 'required|numeric|min:0',
            'km' => 'required|integer|min:0',
            'vehicle_type' => 'required|string|max:255',
        ]);

        Level::create($data);

        return redirect()
            ->route('admin.levels.index')
            ->with('success', 'Level created successfully');
    }

    public function show($id)
    {
        $level = Level::findOrFail($id);

        return view('admin.levels.show', compact('level'));
    }

    public function edit($id)
    {
        $level = Level::findOrFail($id);

        return view('admin.levels.edit', compact('level'));
    }

    public function update(Request $request, $id)
    {
        $level = Level::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'cash_money' => 'required|numeric|min:0',
            'km' => 'required|integer|min:0',
            'vehicle_type' => 'required|string|max:255',
        ]);

        $level->update($data);

        return redirect()
            ->route('admin.levels.index')
            ->with('success', 'Level updated successfully');
    }

    public function destroy($id)
    {
        $level = Level::findOrFail($id);

        $level->delete();

        return redirect()
            ->route('admin.levels.index')
            ->with('success', 'Level deleted successfully');
    }
}
