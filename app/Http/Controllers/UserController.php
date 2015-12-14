<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use Validator;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;

class UserController extends Controller
{

    use AuthenticatesAndRegistersUsers, ThrottlesLogins;

    public function login()
    {
        $username = '';
        return view('user.login', compact('username'));
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

    public function search()
    {
      return view('user.search');
    }

    public function settings()
    {
      return view('user.settings');
    }
}
