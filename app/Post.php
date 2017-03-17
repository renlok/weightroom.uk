<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $primaryKey = 'post_id';
    protected $dates = ['published_at'];
    protected $guarded = ['post_id'];

    public static function prevBlogPostUrl($id)
    {
        $blog = Post::where('post_id', '<', $id)->orderBy('post_id', 'desc')->first();

        return $blog ? $blog->url : '#';
    }

    public static function nextBlogPostUrl($id)
    {
        $blog = Post::where('post_id', '>', $id)->orderBy('post_id', 'asc')->first();

        return $blog ? $blog->url : '#';
    }

    /**
     * Post belongs to a single BlogCategory
     *
     * @returns Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\BlogCategory');
    }

    /**
     * Get all of the post's comments.
     */
    public function comments()
    {
        return $this->morphMany('App\Comment', 'commentable');
    }
}
