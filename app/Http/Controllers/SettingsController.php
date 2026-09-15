<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\User;
use App\Models\DeliveryUser;
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

        $request->merge([
            'work_start_time' => $request->work_start_time
                ? substr($request->work_start_time, 0, 5)
                : null,

            'work_end_time' => $request->work_end_time
                ? substr($request->work_end_time, 0, 5)
                : null,
        ]);
        $data = $request->validate([
            'work_start_time' => 'required|date_format:H:i',
            'work_end_time'   => 'required|date_format:H:i',
            'whatsapp_number' => 'nullable|string',
            'hotline' => 'nullable|string',
            'facebook_link'   => 'nullable|url',
            'instgram_link'   => 'nullable|url',
            'tiktok_link'     => 'nullable|url',

            'logo'            => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',

            'delivery_app_link'   => 'nullable|url',
            'kitchen_app_link'   => 'nullable|url',
            'user_app_link'     => 'nullable|url',

            'user_video'      => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'kitchen_video'   => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'delivery_video'  => 'nullable|mimes:mp4,mov,avi,webm|max:51200',
            'user_rewarded_points' => 'nullable|integer',
            // 'vat_percentage' => 'required|numeric|min:0|max:100',

            'user_contract' => 'nullable|mimes:pdf|max:10240',
            'kitchen_contract' => 'nullable|mimes:pdf|max:10240',
            'delivery_contract' => 'nullable|mimes:pdf|max:10240',
            // 'delivery_meter_price' => 'required|numeric|min:0',
        ]);

        $data['work_start_time'] = substr($data['work_start_time'], 0, 5);
        $data['work_end_time'] = substr($data['work_end_time'], 0, 5);

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

        if ($userContract = $this->uploadFile($request, 'user_contract', 'uploads/settings/contracts')) {
            $data['user_contract'] = $userContract;
        } else {
            unset($data['user_contract']);
        }

        if ($kitchenContract = $this->uploadFile($request, 'kitchen_contract', 'uploads/settings/contracts')) {
            $data['kitchen_contract'] = $kitchenContract;
        } else {
            unset($data['kitchen_contract']);
        }

        if ($deliveryContract = $this->uploadFile($request, 'delivery_contract', 'uploads/settings/contracts')) {
            $data['delivery_contract'] = $deliveryContract;
        } else {
            unset($data['delivery_contract']);
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













    public function privacyPolicyclient()
    {

        return view(
            'privacy',

        );
    }

    public function privacyPolicykitchen()
    {

        return view(
            'privacykitchen',

        );
    }

    public function privacyPolicydriver()
    {

        return view(
            'privacydriver',

        );
    }



    public function terms()
    {

        return view(
            'terms',

        );
    }

    public function termskitchen()
    {

        return view(
            'termskitchen',

        );
    }

    public function termsdriver()
    {

        return view(
            'termsdriver',

        );
    }


    public function deleteUserByEmailPage()
    {
        return view('deleteuseraccount');
    }

    public function deleteUserByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'من فضلك أدخل البريد الإلكتروني.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'لا يوجد مستخدم مسجل بهذا البريد الإلكتروني.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        return back()->with(
            'success',
            "تم حذف المستخدم {$userName} صاحب البريد {$userEmail} بنجاح."
        );
    }

    public function deletedriveraccount()
    {

        return view(
            'deletedriveraccount',

        );
    }








    public function deletedriverByEmailPage()
    {
        return view('deletedriveraccount');
    }

    public function deletedriverByEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ], [
            'email.required' => 'من فضلك أدخل البريد الإلكتروني.',
            'email.email' => 'البريد الإلكتروني غير صحيح.',
        ]);

        $user = DeliveryUser::where('email', $request->email)->first();

        if (!$user) {
            return back()
                ->withInput()
                ->with('error', 'لا يوجد مستخدم مسجل بهذا البريد الإلكتروني.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        return back()->with(
            'success',
            "تم حذف المستخدم {$userName} صاحب البريد {$userEmail} بنجاح."
        );
    }

}
