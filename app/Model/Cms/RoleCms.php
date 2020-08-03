<?php

namespace App\Model\Cms;

use Illuminate\Database\Eloquent\Model;

class RoleCms extends Model
{
    protected $fillable = ['name','description'];

    public function user()
    {
        return $this->hasOne('App\Model\Cms\UserCms');
    }

    public function users()
    {
        return $this->hasMany('App\Model\Cms\UserCms');
    }
}
