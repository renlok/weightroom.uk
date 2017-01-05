<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Post.php

class BlogController extends Controller
{
    public function viewBlog()
    {
        $posts = Post::where('id', '>', 0)->paginate(5);
        return view('blog', compact('posts'));
    }

    public function viewBlogPost($url)
    {
        $post = Post::where('url', $url)->first();
        $prev_url = Post::prevBlogPostUrl($post->id);
        $next_url = Post::nextBlogPostUrl($post->id);

        return view('blogPost', compact('prev_url', 'next_url', 'post'));
    }
}
