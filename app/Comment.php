<?php

namespace App;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Comment extends Model
{
    use SoftDeletes;

    protected $primaryKey = 'comment_id';
    protected $dates = ['comment_date'];
    protected $guarded = ['comment_id'];
    protected $appends = ['user_name'];

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
        return $this->hasMany('App\Comment','parent_id', 'comment_id')->withTrashed();
    }
}
