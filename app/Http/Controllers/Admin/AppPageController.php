<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppPage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;

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





    public function appPage(Request $request): JsonResponse
{
    $validated = $request->validate([
        'app_type' => [
            'required',
            Rule::in(['client', 'kitchen', 'delivery']),
        ],
        'page_type' => [
            'required',
            Rule::in(['privacy', 'terms']),
        ],
    ]);

    $page = AppPage::where('app_type', $validated['app_type'])
        ->where('page_type', $validated['page_type'])
        ->where('is_active', true)
        ->first();

    if (!$page) {
        return response()->json([
            'status' => false,
            'message' => 'Page not found.',
            'data' => null,
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Success',
        'data' => [
            'id' => $page->id,
            'app_type' => $page->app_type,
            'page_type' => $page->page_type,
            'title_ar' => $page->title_ar,
            'title_en' => $page->title_en,
            'content_ar' => $page->content_ar,
            'content_en' => $page->content_en,
        ],
    ]);
}

}
