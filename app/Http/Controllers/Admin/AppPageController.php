<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppPageController extends Controller
{
    public function index()
    {
        $pages = AppPage::latest()->get();

        return view('admin.app_pages.index', compact('pages'));
    }

    public function create()
    {
        return view('admin.app_pages.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'app_type' => [
                'required',
                Rule::in(['client', 'kitchen', 'delivery']),
            ],
            'page_type' => [
                'required',
                Rule::in(['privacy', 'terms']),
                Rule::unique('app_pages')->where(function ($query) use ($request) {
                    return $query->where('app_type', $request->app_type);
                }),
            ],
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_ar' => 'required|string',
            'content_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        AppPage::create($data);

        return redirect()
            ->route('app-pages.index')
            ->with('success', 'تم إضافة الصفحة بنجاح');
    }

    public function edit(AppPage $appPage)
    {
        return view('admin.app_pages.edit', compact('appPage'));
    }

    public function update(Request $request, AppPage $appPage)
    {
        $data = $request->validate([
            'app_type' => [
                'required',
                Rule::in(['client', 'kitchen', 'delivery']),
            ],
            'page_type' => [
                'required',
                Rule::in(['privacy', 'terms']),
                Rule::unique('app_pages')
                    ->where(function ($query) use ($request) {
                        return $query->where('app_type', $request->app_type);
                    })
                    ->ignore($appPage->id),
            ],
            'title_ar' => 'required|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'content_ar' => 'required|string',
            'content_en' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $data['is_active'] = $request->has('is_active');

        $appPage->update($data);

        return redirect()
            ->route('app-pages.index')
            ->with('success', 'تم تعديل الصفحة بنجاح');
    }

    public function destroy(AppPage $appPage)
    {
        $appPage->delete();

        return redirect()
            ->route('app-pages.index')
            ->with('success', 'تم حذف الصفحة بنجاح');
    }

}
