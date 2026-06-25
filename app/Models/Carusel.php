<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Carusel extends Model
{
    protected $table = 'carusel';
    protected $fillable = ['image'];

    public function area()
{
    return $this->belongsTo(Area::class);
}
}
