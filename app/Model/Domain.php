<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Domain extends Model
{
    protected $fillable = [
        'locale',
        'nome',
    ];

    public function urls()
    {
        return $this->hasMany('App\Model\Url');
    }
}
