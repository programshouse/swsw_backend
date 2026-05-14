<?php

namespace App\Http\Controllers;

use App\Http\Resources\WorkDayResource;
use App\Models\WorkDay;
use Illuminate\Http\Request;

class WorkingDayController extends Controller
{
    public function index()
    {

        $work_days = WorkDay::get();

        return WorkDayResource::collection($work_days);
    }


    // public function store(Request $request)
    // {

    //     $validated = $request->validate([
    //         'value' => 'required|string|max:255|unique:work_days,value',
    //     ]);

    //     $workday = WorkDay::create([
    //         'value' => trim($validated['value'])
    //     ]);


    //     return response()->json([
    //         'message' => 'workday created successfully',
    //     ], 201);
    // }


    // public function destroy(Request $request, WorkDay $workday)
    // {

    //     $workday->delete();

    //     return response()->json([
    //         'message' => 'workday deleted successfully',
    //     ] , 200);
    // }


    public function GetAll()
    {
        $work_days = WorkDay::latest()->get();

        return view('admin.workdays.index', compact('work_days'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'value' => 'required|string|max:255|unique:work_days,value',
        ]);

        WorkDay::create([
            'value' => trim($validated['value'])
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إضافة يوم العمل بنجاح');
    }

    public function destroy(Request $request, WorkDay $workday)
    {
        $workday->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف يوم العمل بنجاح');
    }
}
