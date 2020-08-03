<?php
namespace App\Model\Cms;

use Illuminate\Database\Eloquent\Model;


class CmsClearpassword extends Model
{
    protected $fillable = [
        'user_id',
        'password'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\Cms\UserCms');
    }
}