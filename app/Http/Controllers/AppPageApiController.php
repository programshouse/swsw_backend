<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppPage;


class AppPageApiController extends Controller
{
    public function show($appType, $pageType)
    {
        $page = AppPage::where('app_type', $appType)
            ->where('page_type', $pageType)
            ->where('is_active', true)
            ->first();

        if (!$page) {
            return response()->json([
                'status' => false,
                'message' => 'Page not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
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
