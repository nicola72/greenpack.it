<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = [
        'role_id',
        'parent_id',
        'nome',
        'icon',
        'label',
        'stato'
    ];

    public function configs()
    {
        return $this->hasMany('App\Model\ModuleConfig','module_id')->orderBy('nome');
    }
}
