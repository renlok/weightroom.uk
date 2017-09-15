<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BlogCategory extends Model
{
    protected $fillable = ['category'];

    /**
     * a log exercise can have many log items
     *
     * @returns Illuminate\Database\Eloquent\Relations\hasMany
     */
    public function posts()
    {
        return $this->hasMany('App\Post', 'category_id', 'id');
    }
}
