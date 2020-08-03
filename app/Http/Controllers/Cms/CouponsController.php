<?php

namespace App\Http\Controllers\Cms;

use App\Model\Coupon;
use App\Model\Website\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Carbon\Carbon;


class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $coupons = Coupon::all();
        $params = [
            'title_page' => 'Coupons',
            'coupons' => $coupons,
        ];
        return view('cms.coupons.index',$params);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $users = User::all();
        $params = [
            'form_name' => 'form_create_coupon',
            'users' => $users
        ];
        return view('cms.coupons.create',$params);
    }

    public function store(Request $request)
    {
        //vedo se il codice già esiste
        $codice = $request->codice;
        $old_coupon = Coupon::where('codice',$codice)->first();
        if($old_coupon)
        {
            return ['result' => 0,'msg' => 'Errore! Questo codice è già utilizzato da un\'altro coupon'];
        }

        try{
            $coupon = new Coupon();
            $coupon->user_id = $request->user_id;
            $coupon->codice = $request->codice;
            $coupon->tipo_sconto = $request->tipo;
            $coupon->sconto = str_replace(',','.',$request->sconto);
            if($request->valido_da != '')
            {
                $coupon->valido_da = Carbon::createFromFormat('d-m-Y', $request->valido_da)->format('Y-m-d');
            }
            if($request->valido_fino_a != '')
            {
                $coupon->valido_fino_a = Carbon::createFromFormat('d-m-Y', $request->valido_fino_a)->format('Y-m-d');
            }

            $coupon->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = url('/cms/coupons');
        return ['result' => 1,'msg' => 'Elemento inserito con successo!','url' => $url];
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $coupon = Coupon::find($id);
        $users = User::all();
        $params = [
            'coupon' => $coupon,
            'users' => $users,
            'form_name' => 'form_edit_coupon'
        ];

        return view('cms.coupons.edit',$params);
    }

    public function update(Request $request, $id)
    {
        $coupon = Coupon::find($id);
        try{
            $coupon->user_id = $request->user_id;
            $coupon->codice = $request->codice;
            $coupon->tipo_sconto = $request->tipo;
            $coupon->sconto = str_replace(',','.',$request->sconto);
            if($request->valido_da != '')
            {
                $coupon->valido_da = Carbon::createFromFormat('d-m-Y', $request->valido_da)->format('Y-m-d');
            }
            if($request->valido_fino_a != '')
            {
                $coupon->valido_fino_a = Carbon::createFromFormat('d-m-Y', $request->valido_fino_a)->format('Y-m-d');
            }
            $coupon->save();

        }
        catch(\Exception $e){

            return ['result' => 0,'msg' => $e->getMessage()];
        }

        $url = route('cms.coupons');
        return ['result' => 1,'msg' => 'Elemento aggiornato con successo!','url' => $url];
    }

    public function destroy($id)
    {
        $coupon = Coupon::find($id);
        $coupon->delete();

        return back()->with('success','Elemento cancellato!');
    }
}
