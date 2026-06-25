<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {

        $category = Category::get();

        return response()->json([
            'categories' => $category
        ], 201);
    }


  


   



    public function GetAll()
    {
        $categories = Category::latest()->get();

        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'name' => $validated['name']
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
