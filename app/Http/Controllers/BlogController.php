<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Post;
use Carbon\Carbon;

class BlogController extends Controller
{
    public function viewBlog()
    {
        $posts = Post::where('post_id', '>', 0)->paginate(5);
        return view('blog.blog', compact('posts'));
    }

    public function viewBlogPost($url)
    {
        $post = Post::where('url', $url)->first();
        $prev_url = Post::prevBlogPostUrl($post->id);
        $next_url = Post::nextBlogPostUrl($post->id);

        return view('blog.blogPost', compact('prev_url', 'next_url', 'post'));
    }

    public function getAddBlogPost()
    {
        $blog_id = 0;
        $blog_name = '';
        $blog_description = '';
        $blog_content = '';
        $blog_published_at = 'now';
        return view('admin.editBlogPost', compact('blog_id', 'blog_name', 'blog_description', 'blog_content', 'blog_published_at'));
    }

    public function postAddBlogPost(Request $request)
    {
        $blog = new Post;
        $blog->title = $request->input('blog_name');
        $blog->url = str_slug($request->input('blog_name'));
        $blog->description = $request->input('blog_description');
        $blog->markdown = $request->input('blog_content');
        $blog->content = Markdown::convertToHtml($request->input('blog_content'));
        $blog->category_id = 0; // TODO
        $blog->published_at = Carbon::parse($request->input('blog_published_at'));
        $blog->save();

        return redirect()
            ->route('adminListBlogPosts')
            ->with(['flash_message' => "Blog Post Added"]);
    }

    public function getEditBlogPost($post_id)
    {
        $blog = Post::where('post_id', $post_id)->firstorfail();
        $blog_id = $post_id;
        $blog_name = $blog->title;
        $blog_description = $blog->description;
        $blog_content = $blog->markdown;
        $blog_published_at = $blog->published_at;
        return view('admin.editBlogPost', compact('blog_id', 'blog_name', 'blog_description', 'blog_content', 'blog_published_at'));
    }

    public function postEditBlogPost($post_id, Request $request)
    {
        $blog = Post::where('post_id', $post_id)
                ->update([
                    'title' => $request->input('blog_name'),
                    'url' => str_slug($request->input('blog_name')),
                    'description' => $request->input('blog_description'),
                    'content' => Markdown::convertToHtml($request->input('blog_content')),
                    'markdown' => $request->input('blog_content'),
                    'published_at' => Carbon::parse($request->input('blog_published_at'))
                ]);

        return redirect()
            ->route('adminListBlogPosts')
            ->with(['flash_message' => "Blog Post Edited"]);
    }

    public function getDeleteBlogPost($post_id)
    {
        Post::where('post_id', $post_id)->delete();
        return redirect()
            ->route('adminListBlogPosts')
            ->with(['flash_message' => "Blog Post Deleted"]);
    }

    public function getListBlogPosts()
    {
        $posts = Post::all();
        return view('admin.listBlogPosts', compact('posts'));
    }
}
