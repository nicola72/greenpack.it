<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //decommentare per fare sincronizzazione
    //public $timestamps = false;

    protected $dates = ['data_nascita'];

    protected $fillable = [
        'id',
        'user_id',
        'spese_spedizione',
        'spese_conf_regalo',
        'spese_contrassegno',
        'sconto',
        'modalita_pagamento',
        'stato_pagamento',
        'idtranspag',
        'imponibile',
        'iva',
        'sconto_iva',
        'importo',
        'data_nascita',
        'luogo_nascita',
        'locale'
    ];

    public function orderDetails()
    {
        return $this->hasMany('App\Model\OrderDetail');
    }

    public function orderShipping()
    {
        return $this->hasOne('App\Model\OrderShipping');
    }

    public function user()
    {
        return $this->belongsTo('App\Model\Website\User');
    }

}