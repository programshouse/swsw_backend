<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsApiController extends Controller
{
     public function index()
    {
        $settings = Setting::first();

        if (!$settings) {
            return response()->json([
                'status' => true,
                'data' => null,
            ]);
        }

        $prefix = url('/public');

        return response()->json([
            'status' => true,
            'data' => [
                'whatsapp_number' => $settings->whatsapp_number,
                'facebook_link'   => $settings->facebook_link,
                'instgram_link'   => $settings->instgram_link,
                'tiktok_link'     => $settings->tiktok_link,

                'logo'            => $settings->logo ? $prefix . '/' . $settings->logo : null,

                'user_video'      => $settings->user_video ? $prefix . '/' . $settings->user_video : null,
                'kitchen_video'   => $settings->kitchen_video ? $prefix . '/' . $settings->kitchen_video : null,
                'delivery_video'  => $settings->delivery_video ? $prefix . '/' . $settings->delivery_video : null,
            ],
        ]);
    }
}
