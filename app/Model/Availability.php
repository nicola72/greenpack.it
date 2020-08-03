<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $fillable = [
        'nome_it',
        'nome_en',
        'nome_de',
        'nome_fr',
        'nome_es',
        'nome_ru',
        'stato'
    ];

    public function product()
    {
        return $this->hasOne('App\Model\Product');
    }

    public function products()
    {
        return $this->hasMany('App\Model\Products');
    }
}
