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
    ];
}
