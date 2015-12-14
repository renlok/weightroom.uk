<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class UserController extends Controller
{
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
    }

    public function logout()
    {
        Auth::logout();

    }

    public function register()
    {
      return view('user.register');
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
