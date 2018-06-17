<?php
namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\User;
use App\Admin;
use App\Log;

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
        $user_id = User::where('user_name', $user_name)->value('user_id');
        if ($user_id == null) {
            return response()->json(['error' => 1, 'errors'=>'Cannot find user'], 401);
        }
        $log = Log::with('log_exercises.log_items', 'log_exercises.exercise')->where('log_date', $log_date)->where('user_id', $user_id)->get();
        if ($log == null) {
            //try and get bodyweight
            if ($bodyweight = Log::getlastbodyweight(Auth::user()->user_id, $date)->value('log_weight')) {
                return response()->json(['log_data' => ['log_weight' => $bodyweight]]);
            } else {
                return response()->json(['log_data' => null]);
            }
        } else {
            return response()->json(['log_data' => $log]);
        }
    }

    public function getCalenderData($user_name) {
        $user_id = User::where('user_name', $user_name)->value('user_id');
        $log_dates = Log::where('user_id', $user_id)->pluck('log_date')->map(function($item){
            return $item->format('D M d Y');
        });
        return json_encode($log_dates);
    }
}
