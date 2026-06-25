<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::first();

        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'whatsapp_number' => 'nullable|string',
            'facebook_link'   => 'nullable|url',
            'instgram_link'   => 'nullable|url',
            'tiktok_link'     => 'nullable|url',

            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'user_video'      => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'kitchen_video'   => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'delivery_video'  => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
        ]);

        $settings = Setting::first();

        if (!$settings) {
            $settings = new Setting();
        }

        if ($logo = $this->uploadFile($request, 'logo', 'uploads/settings')) {
            $data['logo'] = $logo;
        } else {
            unset($data['logo']);
        }

        if ($userVideo = $this->uploadFile($request, 'user_video', 'uploads/settings/videos')) {
            $data['user_video'] = $userVideo;
        } else {
            unset($data['user_video']);
        }

        if ($kitchenVideo = $this->uploadFile($request, 'kitchen_video', 'uploads/settings/videos')) {
            $data['kitchen_video'] = $kitchenVideo;
        } else {
            unset($data['kitchen_video']);
        }

        if ($deliveryVideo = $this->uploadFile($request, 'delivery_video', 'uploads/settings/videos')) {
            $data['delivery_video'] = $deliveryVideo;
        } else {
            unset($data['delivery_video']);
        }

        $settings->fill($data);
        $settings->save();

        return back()->with('success', 'Settings updated successfully');
    }

    private function uploadFile(Request $request, string $field, string $folder): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $file = $request->file($field);

        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = strtolower($file->getClientOriginalExtension());

        $safeName = Str::slug($originalName);

        if (empty($safeName)) {
            $safeName = $field;
        }

        $fileName = time() . '_' . $field . '_' . $safeName . '.' . $extension;

        /*
         * مهم على السيرفر:
         * لو الدومين شغال من public_html وليس من project/public
         * يبقى لازم نرفع داخل public_html مباشرة.
         */
        $publicHtmlPath = base_path('../public_html/' . $folder);
        $laravelPublicPath = public_path($folder);

        if (is_dir(base_path('../public_html'))) {
            $uploadPath = $publicHtmlPath;
        } else {
            $uploadPath = $laravelPublicPath;
        }

        if (!file_exists($uploadPath)) {
            mkdir($uploadPath, 0775, true);
        }

        $file->move($uploadPath, $fileName);

        return $folder . '/' . $fileName;
    }
}