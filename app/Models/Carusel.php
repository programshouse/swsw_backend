<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carusel extends Model
{
    protected $table = 'carusel';
    protected $fillable = ['image','area_id','kitchen_id'];

    public function area()
{
    return $this->belongsTo(Area::class);
}

public function kitchen()
{
    return $this->belongsTo(User::class, 'kitchen_id');
}
}
