<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    protected $fillable = [
        'id',
        'locale',
        'title',
        'description',
        'h1',
        'h2',
        'alt',
        'keywords',
        'stato'
    ];

    public function url()
    {
        return $this->hasOne('App\Model\Url');
    }
}
