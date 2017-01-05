<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{

    protected $fillable = [
        'url',
        'title',
        'description',
        'content',
        'category_id'
    ];

    public static function prevBlogPostUrl($id)
    {
        $blog = Post::where('id', '<', $id)->orderBy('id', 'desc')->first();

        return $blog ? $blog->url : '#';
    }

    public static function nextBlogPostUrl($id)
    {
        $blog = Post::where('id', '>', $id)->orderBy('id', 'asc')->first();

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
}
