<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template_log_items extends Model
{
    protected $primaryKey = 'logtempitem_id';
    protected $guarded = ['logtempitem_id'];

    /**
     * Template_log_items belongs to a single Template_log_exercise
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function template_log_exercise()
    {
        return $this->belongsTo('App\Template_log_exercise', 'logtempex_id', 'logtempex_id');
    }
}
