<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Auth;

class UpdateUserSettingsRequest extends Request
{
    /**
    * Get the validation rules that apply to the request.
    *
    * @return array
    */
    public function rules()
    {
        return [
            'gender' => 'required|in:m,f',
            'bodyweight' => 'required|numeric',
            'weightunit' => 'required|in:kg,lb',
            'weekstart' => 'required|boolean',
            'squat' => 'required|exists:exercises,exercise_id,user_id,'.Auth::user()->user_id,
            'bench' => 'required|exists:exercises,exercise_id,user_id,'.Auth::user()->user_id,
            'deadlift' => 'required|exists:exercises,exercise_id,user_id,'.Auth::user()->user_id,
            'snatch' => 'required|exists:exercises,exercise_id,user_id,'.Auth::user()->user_id,
            'cnj' => 'required|exists:exercises,exercise_id,user_id,'.Auth::user()->user_id,
            'volumeincfails' => 'required|boolean',
            'viewintensityabs' => 'required|in:p,a,h',
            'limitintensity' => 'required|between:0,100',
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
        return Auth::check();
    }

    /**
    * Get the error messages for the defined validation rules.
    *
    * @return array
    */
    public function messages()
    {
        return [
            'squat.exists' => 'The squat variation selected is invalid',
            'bench.exists' => 'The bench press variation selected is invalid',
            'deadlift.exists' => 'The deadlift variation selected is invalid',
            'snatch.exists' => 'The snatch variation selected is invalid',
            'cnj.exists' => 'The clean and jerk variation selected is invalid',
        ];
    }
}
