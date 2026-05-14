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


    // public function store(Request $request)
    // {

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //     ]);

    //     $category = Category::create([
    //         'name' => $validated['name']
    //     ]);

    //     return response()->json([
    //         'message' => 'category created successfully',
    //         'category' => $category
    //     ], 201);
    // }


    // public function destroy(Request $request, Category $category)
    // {

    //     $category->delete();

    //     return response()->json([
    //         'message' => 'category deleted successfully',
    //     ], 200);
    // }



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
