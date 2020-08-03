<?php
namespace App\Model\Website;

use Illuminate\Database\Eloquent\Model;


class Clearpassword extends Model
{
    protected $fillable = [
        'user_id',
        'password'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\Website\User');
    }
}