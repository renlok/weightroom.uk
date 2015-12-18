<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Validator;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;
    protected $redirectPath = '/dashboard';
    protected $loginPath = 'user/login';

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => 'getLogout']);
    }

    public function login()
    {
        return view('user.login');
    }

    public function login_do (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|max:255',
            'password' => 'required|min:6',
        ]);

        if ($validator->fails())
        {
            return redirect('user/login')
                ->withInput()
                ->withErrors($validator);
        }

        if (Auth::attempt(['user_name' => $username, 'password' => $password], $remember)) {
            // Authentication passed...
            return redirect()->intended('dashboard');
        }
    }

    public function logout()
    {
        Auth::logout();

    }

    public function register()
    {
      return view('user.register');
    }

    public function register_do (Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:255',
            'email' => 'required|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
            'invcode' => 'required',
        ]);

        if ($validator->fails())
        {
            return redirect('user/register')
                ->withInput()
                ->withErrors($validator);
        }

        // TODO: make this work
        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        if (Auth::attempt(['user_name' => $username, 'password' => $password], $remember)) {
            // Authentication passed...
            return redirect()->intended('dashboard');
        }
    }
}
