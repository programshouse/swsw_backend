<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Shift;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->get();

        return view('admin.shifts.index', compact('shifts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
              'name_ar' => 'required|string|max:255',
            'from_time' => 'required',
            'to_time' => 'required',
        ]);

        Shift::create([
            'name_en' => $request->name_en,
             'name_ar' => $request->name_ar,
            'from_time' => $request->from_time,
            'to_time' => $request->to_time,
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إضافة الشيف بنجاح');
    }

    public function delete($id)
    {
        $shift = Shift::findOrFail($id);

        $shift->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف الشيف بنجاح');
    }
}
