<?php

namespace App\Http\Controllers;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use Auth;
use Validator;
use App\Admin;
use App\User;
use App\Invite_code;
use Illuminate\Support\Collection;

class LoginController extends Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->middleware('guest', ['except' => 'getLogout']);
	}

	public function getLogin()
	{
		return view('user.login');
	}

	public function postLogin(LoginRequest $request)
	{
		$input = $request->all();

		if (Auth::attempt(['user_name' => $input['username'], 'password' => $input['password']], isset($input['rememberme']))) {
			// Authentication passed...
			return redirect()->intended('dashboard');
		}
		else
		{
			return redirect('login')
				->withInput()
				->with([
					'flash_message' => 'Email/password wrong, or account not activated.',
					'flash_message_type' => 'danger'
				]);
		}
	}

	public function getLogout()
	{
		Auth::logout();
		return redirect('/');
	}

	public function getRegister(Request $request)
	{
		$invcode = $request->input('invcode', (($request->old('invcode') != null) ? $request->old('invcode') : ''));
		$email = $request->input('user_email', (($request->old('user_email') != null) ? $request->old('user_email') : ''));
		return view('user.register', compact('invcode', 'email'));
	}

	public function postRegister(RegisterRequest $request)
	{
		$input = $request->all();

		if (Admin::InvitesEnabled())
		{
			// is this a valid invite code?
			$invite_code = Invite_code::isvalid($input['invcode'])->first();
			if ($invite_code == null)
			{
				return redirect('register')
					->withInput()
					->with([
						'flash_message' => 'Invalid invite code.',
						'flash_message_type' => 'danger'
					]);
			}

			// Account created remove use from invite code
			Invite_code::where('code_id', $invite_code->code_id)->decrement('code_uses');
		}

		User::create([
			'user_name' => $input['user_name'],
			'user_email' => $input['user_email'],
			'email' => $input['user_email'],
			'user_password' => bcrypt($input['password']),
			'user_invitedcode' => $input['invcode']
		]);

		if (Auth::attempt(['user_name' => $input['user_name'], 'password' => $input['password']])) {
			// Authentication passed...
			return redirect()->intended('dashboard');
		}
	}
}
