<?php

namespace App\Http\Controllers;

use App\Models\UserPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPostController extends Controller
{
    public function index(Request $request): View
    {
        $posts = UserPost::query()
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return view('user-posts.index', ['posts' => $posts]);
    }

    public function create(): View
    {
        return view('user-posts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:case,tool,experience,resource,question'],
            'title' => ['required', 'string', 'min:4', 'max:255'],
            'content' => ['required', 'string', 'min:20', 'max:50000'],
            'visibility' => ['required', 'in:public,vip,private'],
        ]);

        UserPost::query()->create([
            'user_id' => $request->user()->id,
            'type' => $data['type'],
            'title' => $data['title'],
            'content' => $data['content'],
            'visibility' => $data['visibility'],
            'status' => 'pending',
        ]);

        return redirect()->route('user-posts.index')->with('success', '投稿已提交，审核通过后将公开');
    }
}
