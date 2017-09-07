<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Template_purchase extends Model
{
    protected $primaryKey = 'template_purchase_id';
    protected $guarded = ['template_purchase_id'];

    /**
     * template purchase belongs to a single user
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function user()
    {
        return $this->belongsTo('App\User');
    }

    /**
     * template purchase belongs to a single template
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function template()
    {
        return $this->belongsTo('App\Template');
    }
}
