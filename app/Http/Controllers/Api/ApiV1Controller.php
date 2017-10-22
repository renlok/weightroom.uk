<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\User;
use App\Admin;

use Validator;

class ApiV1Controller extends Controller
{
    public function register(RegisterRequest $request) {
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'user_email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);
        if ($validator->fails()) {
            return response()->json(['error' => 1, 'errors'=>$validator->errors()], 401);
        }
        $input = $request->all();

        $user = new User;
        $user->user_name = $input['user_name'];
        $user->user_email = $input['user_email'];
        $user->email = $input['user_email'];
        $user->user_password = bcrypt($input['password']);
        $user->user_invitedcode = (Admin::InvitesEnabled()) ? $input['invcode'] : '';
        if ($user->save()) {
            return response()->json([
                'error' => '0',
                'username' => $input['user_name']
            ]);
        } else {
            return response()->json([
                'error' => '1',
                'errors' => 'Cannot create user'
            ], 401);
        }
    }

    public function getLogData($user_name, $log_date) {
        $user = User::with('logs.log_exercises.log_items', 'logs.log_exercises.exercise')
            ->where('user_name', $user_name)->first();
        if ($user == null) {
            return response()->json(['error' => 1, 'errors'=>'Cannot find user'], 401);
        }
        $log = $user->logs()->where('log_date', $log_date)->first();
        if ($log == null) {
            return response()->json(['log_data' => null], 401);
        } else {
            return response()->json(['log_data' => $log->toJson()]);
        }
    }
}
