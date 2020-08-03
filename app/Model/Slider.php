<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Slider extends Model
{
    protected $fillable = [
        'nome',
        'visibile'
    ];

    public function images()
    {
        return $this->morphMany('App\Model\File','fileable');
    }
}
