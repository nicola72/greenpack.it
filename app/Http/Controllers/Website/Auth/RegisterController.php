<?php

namespace App\Http\Controllers\Website\Auth;

use App\Mail\Registration;
use App\Model\Cart;
use App\Model\Domain;
use App\Model\Macrocategory;
use App\Model\NewsletterSubscriber;
use App\Model\Page;
use App\Model\Website\Clearpassword;
use App\Model\Website\User;
use App\Model\Website\UserDetail;
use App\Service\GoogleRecaptcha;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiter;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Lang;


class RegisterController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->post();
        $config = \Config::get('website_config');
        $secret = $config['recaptcha_secret'];

        if(!GoogleRecaptcha::verifyGoogleRecaptcha($data,$secret))
        {
            return ['result' => 0, 'msg' => trans('msg.il_codice_di_controllo_errato')];
        }

        //inserisco i dati nella sessione
        $request->flash();

        //validazione tramite laravel -- l'errore compare in flash_messages ($errors->any())
        $request->validate([
            'nome'         => 'required',
            'cognome'      => 'required',
            'data_nascita' => 'required',
            'luogo_nascita'=> 'required',
            'email'        => 'required|email:rfc,dns',
        ]);

        //raccolgo i DATI FORM
        $nome           = $request->post('nome');
        $cognome        = $request->post('cognome');
        $email          = $request->post('email');
        $data_nascita   = $request->post('data_nascita');
        $luogo_nascita  = $request->post('luogo_nascita');
        $password       = $request->post('reg_password');
        $retype_password= $request->post('password2');

        //controllo Password
        if($password != $retype_password)
        {
            return ['result' => 0, 'msg'=> trans('msg.la_password_deve_essere_uguale')];
        }

        //controllo Email Già REGISTRATA
        $other_user = User::where('email',$email)->first();
        if($other_user)
        {
            return ['result' => 0, 'msg'=> trans('msg.email_gia_registrata')];
        }

        try
        {
            //creo il NUOVO UTENTE
            $user = new User();
            $user->name = $nome;
            $user->surname = $cognome;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->save();

            //inserisco la password in chiaro
            $clear_pwd = new Clearpassword();
            $clear_pwd->user_id = $user->id;
            $clear_pwd->password = $password;
            $clear_pwd->save();

            //inserisco i DETTAGLI UTENTE
            $userDetails = new UserDetail();
            $userDetails->user_id = $user->id;
            $userDetails->citta_nascita = $luogo_nascita;
            $userDetails->data_nascita = Carbon::createFromFormat('d/m/Y', $data_nascita)->format('Y-m-d');
            $userDetails->save();

            //iscrivo alla LisTA NEWSLETTER
            $userNewsletter = new NewsletterSubscriber();
            $userNewsletter->email = $email;
            $userNewsletter->lang = app()->getLocale();
        }
        catch(\Exception $e)
        {
            if($config['in_sviluppo'])
            {
                return ['result' => 0,'msg' => $e->getMessage()];
            }
            return ['result' => 0,'msg' => trans('msg.errore')];
        }

        //invio email al cliente
        $to = $user->email;
        $mail = new \App\Mail\Registration($user,$password);

        try{
            \Mail::to($to)->send($mail);
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());

        }

        return ['result' => 1, 'msg'=> trans('msg.registrazione_effettuata_con_successo')];
    }

    public function showRetriewPasswordForm()
    {
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'macrocategorie' => $macrocategorie,
            'carts' => Cart::where('session_id',session()->getId())->get(),
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'form_rec_password' => 'form_rec_password',
            'function' => __FUNCTION__ //visualizzato nei meta tag della header
        ];
        return view('website.auth.rec_password',$params);
    }

    public function retriew_password(Request $request)
    {
        $data = $request->post();
        $config = \Config::get('website_config');
        $secret = $config['recaptcha_secret'];

        if(!GoogleRecaptcha::verifyGoogleRecaptcha($data,$secret))
        {
            return ['result' => 0, 'msg' => trans('msg.il_codice_di_controllo_errato')];
        }

        //validazione tramite laravel -- l'errore compare in flash_messages ($errors->any())
        $request->validate([
            'email'        => 'required|email:rfc,dns',
        ]);

        $user = User::where('email',$request->email)->first();
        if(!$user)
        {
            return ['result' => 0, 'msg'=> trans('msg.email_non_registrata')];
        }

        $clear_psw = Clearpassword::where('user_id',$user->id)->first();
        if(!$clear_psw)
        {
            return ['result' => 0, 'msg'=> trans('msg.errore')];
        }

        //invio email al cliente
        $to = $user->email;
        $mail = new \App\Mail\RetriewPassword($user,$clear_psw);

        try{
            \Mail::to($to)->send($mail);
        }
        catch(\Exception $e)
        {
            \Log::error($e->getMessage());

        }

        return ['result' => 1, 'msg'=> trans('msg.ricevera_un_email_con_la_password'),'url'=> url(app()->getLocale().'/login')];

    }

    public function change_account(Request $request)
    {
        if(!\Auth::check())
        {
            return redirect('/');
        }
        $auth_user = \Auth::getUser();

        $data = $request->post();
        $config = \Config::get('website_config');
        $secret = $config['recaptcha_secret'];

        if(!GoogleRecaptcha::verifyGoogleRecaptcha($data,$secret))
        {
            return ['result' => 0, 'msg' => trans('msg.il_codice_di_controllo_errato')];
        }

        //validazione tramite laravel -- l'errore compare in flash_messages ($errors->any())
        $request->validate([
            'email'        => 'required|email:rfc,dns',
            'nome'         => 'required',
            'cognome'      => 'required',
        ]);

        $email   = $request->email;
        $nome    = $request->nome;
        $cognome = $request->cognome;
        $modifica_password = $request->get('mod_pwd',false);


        $user = User::find($auth_user->id);

        //se vuole cambiare l'email
        if($email != $user->email)
        {
            //controllo Email Già REGISTRATA
            $other_user = User::where('email',$email)->first();
            if($other_user)
            {
                return ['result' => 0, 'msg'=> trans('msg.email_gia_registrata')];
            }
        }

        $user->name = $nome;
        $user->surname = $cognome;
        $user->email = $email;

        //vedo se c'è da modificare la password
        if($modifica_password)
        {
            if($modifica_password == 1)
            {
                $vecchia_password = $request->get('password');
                $nuova_password = $request->get('nuova_password');

                //inserisco la password nuova i chiaro
                $clearPwd = Clearpassword::where('user_id',$user->id)->first();
                if($vecchia_password != $clearPwd->password)
                {
                    return ['result' => 0, 'msg'=> trans('msg.password_errata')];
                }
                $clearPwd->password = $nuova_password;
                $clearPwd->save();

                //inserisco la password nuova
                $user->password = Hash::make($nuova_password);
            }
        }

        $user->save();
        return ['result' => 1, 'msg'=> trans('msg.dati_account_modificati_con_successo')];
    }
}