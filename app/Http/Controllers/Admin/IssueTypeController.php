<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IssueType;

class IssueTypeController extends Controller
{
     public function index()
    {
        $issueTypes = IssueType::latest()->get();

        return view('admin.issue-types.index', compact('issueTypes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        IssueType::create($data);

        return redirect()
            ->route('admin.issue-types.index')
            ->with('success', 'تم إضافة نوع المشكلة بنجاح');
    }

    public function update(Request $request, IssueType $issueType)
    {
        $data = $request->validate([
            'name_ar' => 'required|string|max:191',
            'name_en' => 'nullable|string|max:191',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $issueType->update($data);

        return redirect()
            ->route('admin.issue-types.index')
            ->with('success', 'تم تعديل نوع المشكلة بنجاح');
    }

    public function destroy(IssueType $issueType)
    {
        $issueType->delete();

        return redirect()
            ->route('admin.issue-types.index')
            ->with('success', 'تم حذف نوع المشكلة بنجاح');
    }
}
