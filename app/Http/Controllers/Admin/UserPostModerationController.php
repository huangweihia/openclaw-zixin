<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InboxNotification;
use App\Models\UserPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserPostModerationController extends Controller
{
    public function show(UserPost $userPost): View
    {
        $userPost->load('author');

        return view('admin.user-posts.show', ['post' => $userPost]);
    }

    public function index(): View
    {
        $posts = UserPost::query()
            ->where('status', 'pending')
            ->latest()
            ->with('author')
            ->paginate(20);

        return view('admin.user-posts.index', ['posts' => $posts]);
    }

    public function approve(Request $request, UserPost $userPost): RedirectResponse
    {
        if ($userPost->status !== 'pending') {
            return back()->with('error', '该投稿已处理');
        }

        $userPost->forceFill([
            'status' => 'approved',
            'audit_note' => null,
            'audited_by' => $request->user()->id,
            'audited_at' => now(),
        ])->save();

        $content = $userPost->visibility === 'private'
            ? '你的投稿「'.$userPost->title.'」已通过审核（仅自己可见）。'
            : '你的投稿「'.$userPost->title.'」已通过审核，可在投稿广场查看。';

        InboxNotification::query()->create([
            'user_id' => $userPost->user_id,
            'type' => 'user_post_approved',
            'title' => '投稿已通过审核',
            'content' => $content,
            'action_url' => route('posts.show', $userPost),
        ]);

        return back()->with('success', '已通过：「'.$userPost->title.'」');
    }

    public function reject(Request $request, UserPost $userPost): RedirectResponse
    {
        $data = $request->validate([
            'audit_note' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        if ($userPost->status !== 'pending') {
            return back()->with('error', '该投稿已处理');
        }

        $userPost->forceFill([
            'status' => 'rejected',
            'audit_note' => $data['audit_note'],
            'audited_by' => $request->user()->id,
            'audited_at' => now(),
        ])->save();

        InboxNotification::query()->create([
            'user_id' => $userPost->user_id,
            'type' => 'user_post_rejected',
            'title' => '投稿未通过审核',
            'content' => '你的投稿「'.$userPost->title.'」未通过审核。原因：'.$data['audit_note'],
            'action_url' => route('user-posts.index'),
        ]);

        return back()->with('success', '已拒绝该投稿');
    }
}
