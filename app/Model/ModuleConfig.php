<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class ModuleConfig extends Model
{
    protected $fillable = [
        'module_id',
        'nome',
        'desc',
        'type',
        'value'
    ];

    public function module()
    {
        return $this->belongsTo('App\Model\Module');
    }

    public function modules()
    {
        return $this->belongsToMany('App\Model\Module');
    }
}
