<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class OrderShipping extends Model
{
    protected $fillable = [
        'order_id',
        'nome',
        'cognome',
        'email',
        'indirizzo',
        'telefono',
        'cap',
        'citta',
        'provincia',
        'nazione'
    ];

    public function order()
    {
        return $this->belongsTo('App\Model\Order');
    }
}