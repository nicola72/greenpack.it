<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = [
        'product_id',
        'user_id',
        'session_id',
        'qta'
    ];

    public function user()
    {
        return $this->belongsTo('App\Model\Website\User');
    }

    public function product()
    {
        return $this->belongsTo('App\Model\Product');
    }

    public function getCarts()
    {
        if(\Auth::check())
        {
            $user = \Auth::getUser();
            //$carts = Cart::where('user_id',$user->id)->get();
            $products = $this->belongsToMany('App\Model\Product')->where('user_id',$user->id)->get();
        }
        else
        {
            //$carts = Cart::where('session_id',session()->getId())->get();
            $products = $this->belongsToMany('App\Model\Product')->where('session_id',session()->getId())->get();
        }
        return $products;
    }
}
