<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $fillable = [
        'path',
        'type',
        'fileable_id',
        'fileable_type',
        'titolo_it',
        'titolo_en',
        'titolo_de',
        'titolo_fr',
        'titolo_es',
        'titolo_ru',
        'didascalia_it',
        'didascalia_en',
        'didascalia_de',
        'didascalia_fr',
        'didascalia_es',
        'didascalia_ru',
        'order'
    ];

    public function fileable()
    {
        return $this->morphTo();
    }
}
