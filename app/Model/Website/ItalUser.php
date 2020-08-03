<?php

namespace App\Model\Website;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class ItalUser extends Authenticatable
{
    use Notifiable;

    protected $guard = 'website';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'clear_pwd',
        'vede_p_fabbrica',
        'vede_p_vendita',
        'vede_p_netto',
        'vede_sconto_bonifico',
        'sconto',
        'tipo_sconto',
        'sconto_importo',
        'condizione_cliente',
        'condizione_pagamento'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = ['email_verified_at' => 'datetime'];


}
