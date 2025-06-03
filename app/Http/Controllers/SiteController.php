<?php

namespace App\Http\Controllers;

use App\Post;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $posts=Post::orderBy('created_at','desc')
                     ->whereStatus('PUBLISHED')
                     ->take(6)
                     ->get();
        return view('blog.index',['myPosts'=>$posts]);
    }
    public function blog() {

        $posts = Post::orderBy('created_at','desc')
                       ->whereStatus('PUBLISHED')
                       ->paginate(3);
        return view('blog.blog', ['myPosts' => $posts]);
    }

}
