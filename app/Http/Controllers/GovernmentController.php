<?php

namespace App\Http\Controllers;

use App\Models\Government;
use Illuminate\Http\Request;

class GovernmentController extends Controller
{
    public function index()
{
    $governments = Government::latest()->get();

    return view(
        'admin.governments.index',
        compact('governments')
    );
}


    public function store(Request $request)
    {

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:governments,name',
        ]);

        $government = Government::create([
            'name' => trim($validated['name'])
        ]);

        $government->load('areas');

        return response()->json([
            'message' => 'government created successfully',
            'governments' => $government
        ], 201);
    }


    public function destroy(Request $request, Government $government)
    {

        $government->delete();

        return response()->json([
            'message' => 'government deleted successfully',
        ] , 200);
    }

    public function toggleStatus(Request $request, Government $government)
{
    $government->update([
        'is_active' => !$government->is_active
    ]);

    return redirect()
        ->back()
        ->with(
            'success',
            $government->is_active
                ? 'تم تفعيل المحافظة'
                : 'تم تعطيل المحافظة'
        );
}
}
