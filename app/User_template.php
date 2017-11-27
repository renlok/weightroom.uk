<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_template extends Model
{
    protected $primaryKey = 'user_template_id';
    protected $guarded = ['user_template_id'];
    protected $casts = [
        'user_template_data' => 'array',
    ];

    public static function setActive($user_id, $templateId, $templateData) {
        $userTemplate = User_template::where('user_id', $user_id)->first();
        // no template has ever been set for this user
        if ($userTemplate == null) {
            $userTemplate = new User_template;
            $userTemplate->user_id = $user_id;
        }
        $userTemplate->template_id = $templateId;
        $userTemplate->user_template_data = $templateData;
        $userTemplate->save();
    }

    public static function nextActive($user_id) {
        $userTemplate = User_template::where('user_id', $user_id)->first();
        $template_data = $userTemplate->user_template_data;
        // check if there is more than 1 log
        if (count($template_data['log_ids']) > 1) {
            $log_number = array_search($template_data['log'], $template_data['log_ids']);
            // check if current log is last log
            if (isset($template_data['log_ids'][$log_number + 1])) {
                $template_data['log'] = $template_data['log_ids'][$log_number + 1];
            }
            else
            {
                // was on last log cycle to begining
                $template_data['log'] = $template_data['log_ids'][0];
                $template_data['cycle']++;
            }
        } else {
            $template_data['cycle']++;
        }
        $userTemplate->user_template_data = $template_data;
        $userTemplate->save();
    }

    /**
     * user_template has one user
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function user()
    {
        return $this->hasOne('App\User', 'user_id', 'user_id');
    }

    /**
     * user_template has one tempalte
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasOne
     */
    public function template()
    {
        return $this->hasOne('App\Template', 'template_id', 'template_id');
    }
}
