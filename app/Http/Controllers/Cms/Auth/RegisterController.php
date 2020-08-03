<?php

namespace App\Http\Controllers\Cms\Auth;

use App\Model\Cms\RoleCms;
use App\Model\Cms\UserCms;
use App\Model\Cms\CmsClearpassword;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Auth;


class RegisterController extends Controller
{
    //la chiave di configurazione per l'autenticazione
    protected $guard = 'cms';

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/cms';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //se la richiesta non viene da un IP autorizzato esco
        $this->middleware('cms.isipauth');

        $this->middleware('cms.guest');
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function showRegistrationForm()
    {
        $roles = RoleCms::all();
        return view('cms.auth.register',['roles'=> $roles]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:user_cms'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->guard()->login($user);

        return $this->registered($request, $user)
            ?: redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function create(array $data)
    {
        $user = UserCms::create([
            'role_id'  => $data['role_id'],
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        //inserisco la password in chiaro
        $clear_pwd = new CmsClearpassword();
        $clear_pwd->user_id = $user->id;
        $clear_pwd->password = $data['password'];
        $clear_pwd->save();

        return $user;
    }

    /**
     * Get the guard to be used during registration.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard($this->guard);
    }

    /**
     * Get the post register / login redirect path.
     *
     * @return string
     */
    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/home';
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
