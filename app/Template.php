<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $primaryKey = 'template_id';
    protected $guarded = ['template_id'];

    /**
     * templates own a bunch of template_logs
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function template_logs()
    {
        return $this->hasMany('App\Template_log', 'template_log_id');
    }
}
