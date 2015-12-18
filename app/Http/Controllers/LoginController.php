<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Validator;

class LoginController extends Controller
{
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
			return redirect('login')
				->withInput()
				->withErrors($validator);
		}

		$input = $request->all();

		if (Auth::attempt(['user_name' => $input['username'], 'password' => $input['password']], $input['rememberme'])) {
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
			return redirect('register')
				->withInput()
				->withErrors($validator);
		}

		// TODO: check for invite code
		$input = $request->all();
		User::create([
			'user_name' => $input['username'],
			'user_email' => $input['email'],
			'user_password' => bcrypt($input['password']),
		]);

		if (Auth::attempt(['user_name' => $input['username'], 'password' => $input['email']])) {
			// Authentication passed...
			return redirect()->intended('dashboard');
		}
	}
}
