<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Fotogallery extends Model
{
    protected $fillable = [
        'nome_it',
        'nome_en',
        'nome_de',
        'nome_fr',
        'nome_es',
        'desc_it',
        'desc_en',
        'desc_de',
        'desc_fr',
        'desc_es',
        'desc_breve_it',
        'desc_breve_en',
        'desc_breve_de',
        'desc_breve_fr',
        'desc_breve_es',
        'visibile',
        'stato'
    ];
}
