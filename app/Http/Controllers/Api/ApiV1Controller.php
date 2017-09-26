<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\RegisterRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests;

use App\User;
use App\Admin;

class ApiV1Controller extends Controller
{
    public function register(RegisterRequest $request) {
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
                'error' => '1'
            ]);
        }
    }

    public function getLogData($user_name, $log_date) {
        $user = User::with('logs.log_exercises.log_items', 'logs.log_exercises.exercise')
            ->where('user_name', $user_name)->firstOrFail();
        $log = $user->logs()->where('log_date', $log_date)->first();
        return response()->json($log->toJson());
    }
}
