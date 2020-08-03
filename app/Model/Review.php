<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    //decommentare per fare la sincronizzazione
    //public $timestamps = false;

    protected $fillable = [
        'nome',
        'data_evento',
        'messaggio',
        'visibile'
    ];
}