<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use User;

class LoginController extends Controller
{
	public function getLogin()
	{
		return view('user.login');
	}

	public function postLogin(Request $request)
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

		if (Auth::attempt(['user_name' => $input['username'], 'password' => $input['password']], isset($input['rememberme']))) {
			// Authentication passed...
			return redirect()->intended('dashboard');
		}
		else {
			return redirect('login')
				->withInput()
				->with('error', 'Email/password wrong, or account not activated.');
		}
	}

	public function getLogout()
	{
		Auth::logout();

	}

	public function getRegister()
	{
	  return view('user.register');
	}

	public function postRegister(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'user_name' => 'required|unique:users|max:255',
			'user_email' => 'required|unique:users|max:255',
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
		App\User::create([
			'user_name' => $input['user_name'],
			'user_email' => $input['user_email'],
			'user_password' => bcrypt($input['password']),
		]);

		if (Auth::attempt(['user_name' => $input['user_name'], 'password' => $input['password']])) {
			// Authentication passed...
			return redirect()->intended('dashboard');
		}
	}
}
