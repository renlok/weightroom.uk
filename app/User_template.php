<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_template extends Model
{
    protected $primaryKey = 'user_templates';
    protected $guarded = ['user_template_id'];

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
