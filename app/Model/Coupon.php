<?php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $dates = ['data_utilizzo','valido_da','valido_fino_a'];
    protected $fillable = [
        'id',
        'user_id',
        'codice',
        'tipo_sconto',
        'sconto',
        'utilizzato',
        'data_utilizzo',
        'valido_da',
        'valido_fino_a',
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\Website\User');
    }
}
