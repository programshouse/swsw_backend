<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'whatsapp_number',
        'facebook_link',
        'instgram_link',
        'tiktok_link',
        'logo',
        'user_video',
        'kitchen_video',
        'delivery_video',
        'user_app_link',
        'kitchen_app_link',
        'delivery_app_link',
        'work_start_time',
        'work_end_time',
        'user_rewarded_points',
        'vat_percentage',
        'delivery_contract',
        'kitchen_contract',
        'user_contract',
        'delivery_meter_price',
       
    ];


    public static function appLinks()
    {
        $setting = self::first();

        return [
            'user_app_link'     => $setting?->user_app_link,
            'kitchen_app_link'  => $setting?->kitchen_app_link,
            'delivery_app_link' => $setting?->delivery_app_link,
        ];
    }
}
