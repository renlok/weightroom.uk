<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $primaryKey = 'template_id';
    protected $guarded = ['template_id'];

    public static function addScore($log_id, $score = 1)
    {
        $template = Template_log::find($log_id)->template;
        $template->template_score += $score;
        $template->save();
    }

    /**
     * templates own a bunch of template_logs
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function template_logs()
    {
        return $this->hasMany('App\Template_log', 'template_id', 'template_id');
    }
}
