<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
   public function index()
{
    $categories = Category::get()->map(function ($category) {
        return [
            'id' => $category->id,
            'name' => $category->name,
        ];
    });

    return response()->json([
        'categories' => $categories,
    ], 200);
}


  


   



    public function GetAll()
    {
        $categories = Category::latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name_en' => 'required|string|max:255',
            'name_ar' => 'required|string|max:255',
        ]);

        Category::create([
            'name_en' => $validated['name_en'],
            'name_ar' => $validated['name_ar']
        ]);

        return redirect()
            ->back()
            ->with('success', 'تم إضافة الفئة بنجاح');
    }

    public function destroy(Request $request, Category $category)
    {
        $category->delete();

        return redirect()
            ->back()
            ->with('success', 'تم حذف الفئة بنجاح');
    }
}
