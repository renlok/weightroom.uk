<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template_log_exercise extends Model
{
    protected $primaryKey = 'logtempex_id';
    protected $guarded = ['logtempex_id'];

    /**
     * template_log_exercises own a bunch of template_log_items
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function template_log_items()
    {
        return $this->hasMany('App\Template_log_items', 'logtempitem_id');
    }

    /**
     * Template_log_exercise belongs to a single Template_log
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function template_log()
    {
        return $this->belongsTo('App\Template_log', 'template_log_id');
    }
}
