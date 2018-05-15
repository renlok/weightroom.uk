<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Admin;

class RegisterRequest extends Request
{
    /**
    * Get the validation rules that apply to the request.
    *
    * @return array
    */
    public function rules()
    {
        return [
            'user_name' => 'required|unique:users|max:255|isurlsafe',
            'user_email' => 'required|unique:users|max:255',
            'password' => 'required|confirmed|min:6',
            'invcode' => (Admin::InvitesEnabled()) ? 'required|isvalid:invite_codes,code,code_uses,code_expires' : '',
        ];
    }

    /**
    * Determine if the user is authorized to make this request.
    *
    * @return bool
    */
    public function authorize()
    {
        // Only allow logged in users
        // return \Auth::check();
        // Allows all users in
        return true;
    }

    /**
    * Get the error messages for the defined validation rules.
    *
    * @return array
    */
    public function messages()
    {
        return [
            'user_name.isurlsafe' => 'You cannot use /, #, \\, ? or & characters in your username',
            'invcode.exists' => 'The invite code is not valid',
        ];
    }
}
