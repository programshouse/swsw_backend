<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
     public function index()
    {
        // هنفترض إن عندك row واحد فقط
        $settings = Setting::first();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'whatsapp_number' => 'nullable|string',
            'facebook_link'   => 'nullable|url',
            'instgram_link'   => 'nullable|url',
            'tiktok_link'     => 'nullable|url',
        ]);

        $settings = Setting::first();

        if (!$settings) {
            $settings = Setting::create($request->all());
        } else {
            $settings->update($request->all());
        }

        return back()->with('success', 'Settings updated successfully');
    }
}
