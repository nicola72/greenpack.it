<?php

namespace App\Http\Controllers\Website\Auth;

use App\Model\Cart;
use App\Model\Domain;
use App\Model\Macrocategory;
use App\Model\Page;
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

class LoginController extends Controller
{
    //l'url dove verrà reinderizzato se il login a buon fine
    protected $redirectTo = '/';

    //la chiave di configurazione per l'autenticazione
    protected $guard = 'website';

    //numero max di tentativi
    protected $maxAttempts = 5;

    protected $decayMinutes = 1;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showLoginAndRegisterForm()
    {
        $macrocategorie = Macrocategory::where('stato',1)->orderBy('order')->get();

        $params = [
            'macrocategorie' => $macrocategorie,
            'carts' => Cart::where('session_id',session()->getId())->get(),
            'macro_request' => null, //paramtero necessario per stabilire il collapse del menu a sinistra
            'form_login' => 'form_login',
            'form_registrazione' => 'form_registrazione',
        ];
        return view('website.auth.login_and_registration',$params);
    }


    public function showLoginForm()
    {
        return view('website.auth.login');
    }


    public function login(Request $request)
    {
        //controlla se i dati per il login sono in forma giusta
        $this->validateLogin($request);

        // i tentativi di login
        if ($this->hasTooManyLoginAttempts($request))
        {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        //provo a effettare il login
        if ($this->attemptLogin($request))
        {
            //se andato a buon fine
            return $this->sendLoginResponse($request);
        }

        //Se il login non ha avuto successo incrementiamo il numero dei tentativi
        //e redirect alla pagina di login, se non ha superato il numero max di tentativi altrimenti bloccato
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function validateLogin(Request $request)
    {
        $request->validate(['email' => 'required|string','password' => 'required|string']);
    }

    /**
     * Attempt to log the user into the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function attemptLogin(Request $request)
    {
        return $this->guard()->attempt($this->credentials($request), $request->filled('remember'));
    }

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only('email', 'password');
    }


    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        $this->clearLoginAttempts($request);

        $website_config = \Config::get('website_config');
        $domain = Domain::where('locale',\App::getLocale())->first();
        $url = $website_config['protocol'].'://www.'.$domain->nome;

        return ['result'=>1, 'msg' => trans('msg.credenziali_corrette'),'url' => $url];
        //return redirect($this->redirectTo);
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return ['result' => 1,'msg'=>trans('msg.credenziali_corrette')];
    }

    /**
     * Get the failed login response instance.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendFailedLoginResponse(Request $request)
    {
        throw ValidationException::withMessages(['email' => [trans('auth.failed')],]);
    }

    /**
     * Log the user out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();

        return redirect('/');
    }

    /**
     * The user has logged out of the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function loggedOut(Request $request)
    {
        //
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->guard);
    }

    /**
     * Determine if the user has too many failed login attempts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function hasTooManyLoginAttempts(Request $request)
    {
        return $this->limiter()->tooManyAttempts($this->throttleKey($request), $this->maxAttempts);
    }

    /**
     * Increment the login attempts for the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function incrementLoginAttempts(Request $request)
    {
        $this->limiter()->hit($this->throttleKey($request), $this->decayMinutes * 60);
    }

    /**
     * Redirect the user after determining they are locked out.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function sendLockoutResponse(Request $request)
    {
        $seconds = $this->limiter()->availableIn($this->throttleKey($request));

        throw ValidationException::withMessages([
            'email' => [Lang::get('auth.throttle', ['seconds' => $seconds])],
        ])->status(Response::HTTP_TOO_MANY_REQUESTS);
    }

    /**
     * Clear the login locks for the given user credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function clearLoginAttempts(Request $request)
    {
        $this->limiter()->clear($this->throttleKey($request));
    }

    /**
     * Fire an event when a lockout occurs.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function fireLockoutEvent(Request $request)
    {
        event(new Lockout($request));
    }

    /**
     * Get the throttle key for the given request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function throttleKey(Request $request)
    {
        return Str::lower($request->input('email')).'|'.$request->ip();
    }

    /**
     * Get the rate limiter instance.
     *
     * @return \Illuminate\Cache\RateLimiter
     */
    protected function limiter()
    {
        return app(RateLimiter::class);
    }
}
