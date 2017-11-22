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

    public static function setActive($userId, $templateId, $templateData) {
        $userTemplate = User_template::where('user_id', $userId)->first();
        // no template has ever been set for this user
        if ($userTemplate == null) {
            $userTemplate = new User_template;
            $userTemplate->user_id = $userId;
        }
        $userTemplate->template_id = $templateId;
        $userTemplate->user_template_data = $templateData;
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
