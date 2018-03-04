<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use Auth;
use App\Post;
use App\Comment;
use Carbon\Carbon;
use Markdown;

class BlogController extends Controller
{
    public static function adminCheck()
    {
        if (Auth::user()->user_id != 1) abort(404);
    }

    public function viewBlog()
    {
        $posts = Post::orderBy('post_id', 'desc')->paginate(10);
        return view('blog.blog', compact('posts'));
    }

    public function viewBlogPost($url)
    {
        $post = Post::where('url', $url)->firstOrFail();
        $prev_url = Post::prevBlogPostUrl($post->post_id);
        $next_url = Post::nextBlogPostUrl($post->post_id);
        $comments = Comment::where('commentable_id', $post->post_id)->where('commentable_type', 'App\Post')->whereNotIn('user_id', User::shadowBanList())->where('parent_id', 0)->orderBy('comment_date', 'asc')->withTrashed()->get();
        if (!isset($commenting))
        {
            $commenting = false;
        }

        return view('blog.blogPost', compact('prev_url', 'next_url', 'post', 'comments', 'commenting'));
    }

    public function getAddBlogPost()
    {
        BlogController::adminCheck();
        $blog_id = 0;
        $blog_name = '';
        $blog_description = '';
        $blog_content = '';
        $blog_published_at = 'now';
        return view('admin.editBlogPost', compact('blog_id', 'blog_name', 'blog_description', 'blog_content', 'blog_published_at'));
    }

    public function postAddBlogPost(Request $request)
    {
        BlogController::adminCheck();
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
        BlogController::adminCheck();
        $blog = Post::where('post_id', $post_id)->firstOrFail();
        $blog_id = $post_id;
        $blog_name = $blog->title;
        $blog_description = $blog->description;
        $blog_content = $blog->markdown;
        $blog_published_at = $blog->published_at;
        return view('admin.editBlogPost', compact('blog_id', 'blog_name', 'blog_description', 'blog_content', 'blog_published_at'));
    }

    public function postEditBlogPost($post_id, Request $request)
    {
        BlogController::adminCheck();
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
        BlogController::adminCheck();
        Post::where('post_id', $post_id)->delete();
        return redirect()
            ->route('adminListBlogPosts')
            ->with(['flash_message' => "Blog Post Deleted"]);
    }

    public function getListBlogPosts()
    {
        BlogController::adminCheck();
        $posts = Post::orderBy('post_id', 'desc')->get();
        return view('admin.listBlogPosts', compact('posts'));
    }
}
