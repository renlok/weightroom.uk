<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $primaryKey = 'log_comment_id';
    protected $dates = ['comment_date', 'log_date'];

    /**
     * Get all of the owning commentable models.
     */
    public function commentable()
    {
        return $this->morphTo();
    }

    /**
     * a parent comment can have many children
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function children()
    {
        $this->hasMany('App\Comment','parent_id');
    }
}
