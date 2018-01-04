<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class TemplateRequest extends Request
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'template_name' => 'required',
            'template_type' => 'in:powerlifting,running,weightlifting,crossfit,bodybuilding,general',
            'log_name.*' => 'required|string',
            'log_week.*' => 'required|numeric',
            'log_day.*' => 'between:1,7',
            'exercise_name.*.*' => 'required|string',
            'item_value' => 'present',
            'item_value.*.*.*' => 'required|numeric',
            'item_plus.*.*.*' => 'numeric',
            'item_reps.*.*.*' => 'required|numeric',
            'item_sets.*.*.*' => 'required|numeric',
            'item_rpe.*.*.*' => 'numeric',
            'item_type.*.*.*' => 'in:W,RM,P,D,T',
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
            'template_name' => 'Template name missing',
            'template_type' => 'Invalid template type',
            'log_name.*' => 'Log names cannot be empty',
            'log_week.*' => 'Week number must be numerical',
            'log_day.*' => 'Invalid day of week',
            'exercise_name.*.*' => 'Exercise names cannot be empty',
            'present' => 'Workout Template cannot be empty',
            'item_value.*.*.*' => 'Weight/Time/Distance must be numerical',
            'item_plus.*.*.*' => 'Additional weight must be numerical',
            'item_reps.*.*.*' => 'Reps must be numerical',
            'item_sets.*.*.*' => 'Sets must be numerical',
            'item_rpe.*.*.*' => 'RPE must be numerical',
            'item_type.*.*.*' => 'Invalid set type',
        ];
    }
}
