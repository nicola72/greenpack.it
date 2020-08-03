<?php
namespace App\Model\Website;

use Illuminate\Database\Eloquent\Model;


class UserDetail extends Model
{
    protected $dates = ['data_nascita'];

    protected $fillable = [
        'user_id',
        'data_nascita',
        'citta_nascita',
        'prov_nascita',
        'citta_residenza',
        'prov_residenza',
        'cap_residenza',
        'via_residenza',
        'nr_residenza'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\Website\User');
    }
}