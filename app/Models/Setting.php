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
        'delivery_video'
    ];
}
