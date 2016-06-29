<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template_log extends Model
{
    protected $primaryKey = 'template_log_id';
    protected $guarded = ['template_log_id'];

    /**
     * template_logs own a bunch of template_log_exercises
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function template_log_exercises()
    {
        return $this->hasMany('App\Template_log_exercise', 'template_log_id');
    }

    /**
     * Template_log belongs to a single Template
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function template_log()
    {
        return $this->belongsTo('App\Template', 'template_id', 'template_id');
    }
}
