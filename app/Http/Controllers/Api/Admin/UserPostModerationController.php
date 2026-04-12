<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\InboxNotification;
use App\Models\User;
use App\Models\UserPost;
use App\Services\UserPostModerationRewards;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserPostModerationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $posts = UserPost::query()
            ->where('status', 'pending')
            ->latest()
            ->with('author:id,name,email')
            ->paginate(20);

        return response()->json($posts);
    }

    public function approve(Request $request, UserPost $userPost): JsonResponse
    {
        if ($userPost->status !== 'pending') {
            return response()->json(['message' => '该投稿已处理'], 422);
        }

        /** @var User $admin */
        $admin = $request->user();
        $this->approveOne($userPost, $admin);

        return response()->json(['ok' => true, 'post' => $userPost->fresh('author')]);
    }

    public function batchApprove(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['integer', 'exists:user_posts,id'],
        ]);

        /** @var User $admin */
        $admin = $request->user();
        $count = 0;

        DB::transaction(function () use ($data, $admin, &$count) {
            $posts = UserPost::query()
                ->whereIn('id', $data['ids'])
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            foreach ($posts as $post) {
                $this->approveOne($post, $admin);
                $count++;
            }
        });

        return response()->json(['ok' => true, 'processed' => $count]);
    }

    public function batchReject(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:50'],
            'ids.*' => ['integer', 'exists:user_posts,id'],
            'audit_note' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        /** @var User $admin */
        $admin = $request->user();
        $count = 0;

        DB::transaction(function () use ($data, $admin, &$count) {
            $posts = UserPost::query()
                ->whereIn('id', $data['ids'])
                ->where('status', 'pending')
                ->lockForUpdate()
                ->get();

            foreach ($posts as $post) {
                $post->forceFill([
                    'status' => 'rejected',
                    'audit_note' => $data['audit_note'],
                    'audited_by' => $admin->id,
                    'audited_at' => now(),
                ])->save();

                InboxNotification::query()->create([
                    'user_id' => $post->user_id,
                    'type' => 'user_post_rejected',
                    'title' => '投稿未通过审核',
                    'content' => '你的投稿「'.$post->title.'」未通过审核。原因：'.$data['audit_note'],
                    'action_url' => route('user-posts.index'),
                ]);
                $count++;
            }
        });

        return response()->json(['ok' => true, 'processed' => $count]);
    }

    private function approveOne(UserPost $userPost, User $admin): void
    {
        $userPost->forceFill([
            'status' => 'approved',
            'audit_note' => null,
            'audited_by' => $admin->id,
            'audited_at' => now(),
        ])->save();

        UserPostModerationRewards::onApproved($userPost->fresh(['author']));

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
    }

    public function reject(Request $request, UserPost $userPost): JsonResponse
    {
        $data = $request->validate([
            'audit_note' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        if ($userPost->status !== 'pending') {
            return response()->json(['message' => '该投稿已处理'], 422);
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

        return response()->json(['ok' => true]);
    }
}
